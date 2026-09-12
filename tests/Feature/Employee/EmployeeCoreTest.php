<?php

namespace Tests\Feature\Employee;

use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_be_created_with_auto_number_timeline_and_audit(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $this->grant($user, ['employee.create', 'employee.view', 'employee.update']);

        $response = $this->actingAs($user)->postJson('/api/employees', [
            'employee_code' => 'EMP-AZ-001',
            'company_id' => $company->id,
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
            'first_name' => 'Ayesha',
            'last_name' => 'Khan',
            'gender' => 'female',
            'official_email' => 'ayesha.khan@example.com',
            'mobile' => '+92 300 1111111',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.employee_number', 'EMP-000001')
            ->assertJsonPath('data.full_name', 'Ayesha Khan');

        $employeeId = $response->json('data.id');

        $this->assertDatabaseHas('employees', [
            'id' => $employeeId,
            'tenant_id' => $tenant->id,
            'employee_number' => 'EMP-000001',
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('employee_timelines', [
            'employee_id' => $employeeId,
            'event_type' => 'created',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'tenant_id' => $tenant->id,
            'table_name' => 'employees',
            'action' => 'created',
        ]);

        $this->actingAs($user)
            ->getJson('/api/employees?search=Ayesha')
            ->assertOk()
            ->assertJsonPath('data.0.full_name', 'Ayesha Khan');

        $this->actingAs($user)
            ->postJson("/api/employees/{$employeeId}/emergency-contacts", [
                'name' => 'Ali Khan',
                'relationship' => 'Brother',
                'mobile' => '+92 300 2222222',
                'priority' => 1,
            ])
            ->assertCreated();
    }

    public function test_sensitive_employee_sections_require_specific_permission(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $this->grant($user, ['employee.create', 'employee.view']);

        $employeeId = $this->actingAs($user)->postJson('/api/employees', [
            'company_id' => $company->id,
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
            'first_name' => 'Bilal',
            'last_name' => 'Ahmed',
        ])->json('data.id');

        $this->actingAs($user)
            ->postJson("/api/employees/{$employeeId}/bank-accounts", [
                'bank' => 'HBL',
                'account_number' => '123456789',
                'account_title' => 'Bilal Ahmed',
            ])
            ->assertForbidden();
    }

    public function test_sections_can_be_updated_and_documents_receive_versions(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $this->grant($user, ['employee.create', 'employee.view', 'employee.update', 'employee.documents']);

        $employeeId = $this->actingAs($user)->postJson('/api/employees', [
            'company_id' => $company->id,
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
            'first_name' => 'Sana',
            'last_name' => 'Ali',
        ])->json('data.id');

        $contactId = $this->actingAs($user)
            ->postJson("/api/employees/{$employeeId}/emergency-contacts", [
                'name' => 'Ahmed Ali',
                'relationship' => 'Brother',
                'mobile' => '+92 300 3333333',
            ])
            ->assertCreated()
            ->json('data.id');

        $this->actingAs($user)
            ->patchJson("/api/employees/{$employeeId}/emergency-contacts/{$contactId}", [
                'mobile' => '+92 300 4444444',
            ])
            ->assertOk()
            ->assertJsonPath('data.mobile', '+92 300 4444444');

        foreach (['resume-v1.pdf', 'resume-v2.pdf'] as $index => $path) {
            $this->actingAs($user)
                ->postJson("/api/employees/{$employeeId}/documents", [
                    'document_type' => 'resume',
                    'title' => 'Current Resume',
                    'file_path' => $path,
                ])
                ->assertCreated()
                ->assertJsonPath('data.version', $index + 1);
        }
    }

    public function test_employee_permission_is_required_for_listing(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->getJson('/api/employees')
            ->assertForbidden();
    }

    /**
     * @param  list<string>  $permissions
     */
    private function grant(User $user, array $permissions): void
    {
        $group = PermissionGroup::query()->firstOrCreate(
            ['name' => 'employee'],
            ['label' => 'Employee Core'],
        );

        foreach ($permissions as $permissionName) {
            $permission = Permission::query()->firstOrCreate(
                ['name' => $permissionName],
                ['permission_group_id' => $group->id, 'label' => $permissionName],
            );

            $user->permissions()->attach($permission);
        }
    }
}
