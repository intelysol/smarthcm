<?php

namespace Tests\Feature\Lifecycle;

use App\Domains\Employee\Models\Employee;
use App\Domains\Lifecycle\Enums\PersonnelActionStatus;
use App\Domains\Lifecycle\Models\PersonnelActionChange;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Models\PersonnelActionType;
use App\Domains\Lifecycle\Services\PersonnelActionReversalService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelActionReversalAndCompensatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_reversal_creates_compensating_action_and_restores_state(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ', 'code' => 'BU-REV']);
        $deptOriginal = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Engineering', 'department_code' => 'ENG-REV']);
        $deptPromoted = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Architecture', 'department_code' => 'ARCH-REV']);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $deptPromoted->id, // Currently in promoted dept
            'employee_code' => 'EMP-REV-1',
            'employee_number' => 'EMP-REV-1',
            'first_name' => 'Stanley',
            'last_name' => 'Hudson',
            'official_email' => 'stanley@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $actionType = PersonnelActionType::create([
            'tenant_id' => $tenant->id,
            'code' => 'PROMOTION',
            'name' => 'Promotion',
        ]);

        $originalAction = PersonnelActionRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'action_type_id' => $actionType->id,
            'request_number' => 'PA-ORIG-01',
            'status' => PersonnelActionStatus::EXECUTED->value,
            'requested_by' => $user->id,
            'requested_at' => now(),
            'effective_date' => now()->toDateString(),
            'executed_at' => now(),
        ]);

        PersonnelActionChange::create([
            'tenant_id' => $tenant->id,
            'personnel_action_request_id' => $originalAction->id,
            'field_name' => 'department_id',
            'old_value' => $deptOriginal->id,
            'new_value' => $deptPromoted->id,
            'old_value_label' => 'Engineering',
            'new_value_label' => 'Architecture',
        ]);

        $reversalService = new PersonnelActionReversalService();

        // Reverse Action
        $reversalRecord = $reversalService->reverseExecutedAction($originalAction, $user, 'Clerical error during departmental assignment');

        $this->assertEquals(PersonnelActionStatus::REVERSED->value, $originalAction->fresh()->status);
        $this->assertNotNull($reversalRecord);
        $this->assertEquals($originalAction->id, $reversalRecord->original_action_id);

        // Verify Core HR state is restored to old_value
        $this->assertEquals($deptOriginal->id, $employee->fresh()->department_id);
    }
}
