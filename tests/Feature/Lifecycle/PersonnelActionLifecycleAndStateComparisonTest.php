<?php

namespace Tests\Feature\Lifecycle;

use App\Domains\Employee\Models\Employee;
use App\Domains\Lifecycle\Enums\PersonnelActionStatus;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Models\PersonnelActionType;
use App\Domains\Lifecycle\Services\PersonnelActionService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelActionLifecycleAndStateComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_personnel_action_creation_state_comparison_and_progression(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-LC-1']);
        $deptOld = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Finance', 'department_code' => 'FIN-01']);
        $deptNew = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Corporate Finance', 'department_code' => 'CFIN-01']);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $deptOld->id,
            'employee_code' => 'EMP-LC-01',
            'employee_number' => 'EMP-LC-01',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'official_email' => 'john.doe@example.com',
            'joining_date' => now()->subYear()->toDateString(),
            'employment_status' => 'active',
        ]);

        $actionType = PersonnelActionType::create([
            'tenant_id' => $tenant->id,
            'code' => 'TRANSFER',
            'name' => 'Department Transfer',
            'category' => 'transfer',
        ]);

        $service = new PersonnelActionService();

        // 1. Create Request
        $request = $service->createRequest($user, [
            'employee_id' => $employee->id,
            'action_type_id' => $actionType->id,
            'effective_date' => now()->toDateString(),
            'reason' => 'Annual strategic reorganization',
            'changes' => [
                [
                    'field_name' => 'department_id',
                    'entity_type' => 'employee',
                    'old_value' => $deptOld->id,
                    'new_value' => $deptNew->id,
                    'old_value_label' => 'Finance',
                    'new_value_label' => 'Corporate Finance',
                ],
            ],
        ]);

        $this->assertInstanceOf(PersonnelActionRequest::class, $request);
        $this->assertMatchesRegularExpression('/^PA-\d{4}-\d{6}$/', $request->request_number);
        $this->assertEquals(PersonnelActionStatus::DRAFT->value, $request->status);

        // 2. Verify Current vs Proposed State
        $this->assertCount(1, $request->changes);
        $change = $request->changes->first();
        $this->assertEquals('department_id', $change->field_name);
        $this->assertEquals($deptOld->id, $change->old_value);
        $this->assertEquals($deptNew->id, $change->new_value);
        $this->assertEquals('Finance', $change->old_value_label);
        $this->assertEquals('Corporate Finance', $change->new_value_label);

        // 3. Submit for approval
        $submitted = $service->submitRequest($request, $user);
        $this->assertEquals(PersonnelActionStatus::PENDING_APPROVAL->value, $submitted->status);
        $this->assertNotNull($submitted->submitted_at);

        // 4. Approve and execute
        $approved = $service->approveRequest($submitted, $user, 'Approved by Director');
        $this->assertEquals(PersonnelActionStatus::EXECUTED->value, $approved->status);
        $this->assertNotNull($approved->approved_at);
        $this->assertNotNull($approved->executed_at);

        // 5. Verify Core HR employee record was updated
        $this->assertEquals($deptNew->id, $employee->fresh()->department_id);
    }
}
