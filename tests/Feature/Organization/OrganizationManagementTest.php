<?php

namespace Tests\Feature\Organization;

use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_search_branch_with_permission(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $this->grant($user, ['branch.create', 'branch.view']);

        $response = $this->actingAs($user)->postJson('/api/organization/branches', [
            'company_id' => $company->id,
            'branch_code' => 'LHR-001',
            'branch_name' => 'Lahore Headquarters',
            'region' => 'Punjab',
            'email' => 'lahore@example.com',
            'phone' => '+92 42 111 000 000',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'status' => 'active',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.attributes.branch_code', 'LHR-001');

        $this->assertDatabaseHas('branches', [
            'tenant_id' => $tenant->id,
            'branch_code' => 'LHR-001',
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'action' => 'created',
            'table_name' => 'branches',
        ]);

        $this->actingAs($user)
            ->getJson('/api/organization/branches?search=Lahore')
            ->assertOk()
            ->assertJsonPath('data.0.attributes.branch_name', 'Lahore Headquarters');
    }

    public function test_permission_is_required_for_organization_entities(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->getJson('/api/organization/branches')
            ->assertForbidden();
    }

    /**
     * @param list<string> $permissions
     */
    private function grant(User $user, array $permissions): void
    {
        $group = PermissionGroup::query()->create([
            'name' => 'organization',
            'label' => 'Organization Management',
        ]);

        foreach ($permissions as $permissionName) {
            $permission = Permission::query()->create([
                'permission_group_id' => $group->id,
                'name' => $permissionName,
                'label' => $permissionName,
            ]);

            $user->permissions()->attach($permission);
        }
    }
}
