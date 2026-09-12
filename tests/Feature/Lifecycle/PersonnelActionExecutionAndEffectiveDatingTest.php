<?php

namespace Tests\Feature\Lifecycle;

use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Models\EmployeeAssignment;
use App\Domains\Lifecycle\Enums\PersonnelActionStatus;
use App\Domains\Lifecycle\Models\PersonnelActionChange;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Models\PersonnelActionType;
use App\Domains\Lifecycle\Services\PersonnelActionExecutionService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelActionExecutionAndEffectiveDatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_atomic_execution_core_hr_mutation_and_idempotency(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-EXEC']);
        $deptA = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Sales', 'department_code' => 'SALES-01']);
        $deptB = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Marketing', 'department_code' => 'MKTG-01']);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $deptA->id,
            'employee_code' => 'EMP-EXEC-1',
            'employee_number' => 'EMP-EXEC-1',
            'first_name' => 'Jim',
            'last_name' => 'Halpert',
            'official_email' => 'jim@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $actionType = PersonnelActionType::create([
            'tenant_id' => $tenant->id,
            'code' => 'TRANSFER',
            'name' => 'Transfer',
        ]);

        $request = PersonnelActionRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'action_type_id' => $actionType->id,
            'request_number' => 'PA-EXEC-01',
            'status' => PersonnelActionStatus::APPROVED->value,
            'requested_by' => $user->id,
            'requested_at' => now(),
            'effective_date' => now()->toDateString(),
        ]);

        PersonnelActionChange::create([
            'tenant_id' => $tenant->id,
            'personnel_action_request_id' => $request->id,
            'field_name' => 'department_id',
            'old_value' => $deptA->id,
            'new_value' => $deptB->id,
            'effective_date' => now()->toDateString(),
        ]);

        $executionService = new PersonnelActionExecutionService();

        // 1. Execute
        $executed = $executionService->execute($request, $user);
        $this->assertEquals(PersonnelActionStatus::EXECUTED->value, $executed->status);
        $this->assertNotNull($executed->executed_at);

        // Core HR check
        $this->assertEquals($deptB->id, $employee->fresh()->department_id);

        // Assignment check
        $assignment = EmployeeAssignment::where('employee_id', $employee->id)->latest()->first();
        $this->assertNotNull($assignment);
        $this->assertEquals($deptB->id, $assignment->department_id);
        $this->assertEquals(now()->toDateString(), $assignment->effective_from->toDateString());

        // 2. Idempotency check: Calling execute again returns existing executed action with no duplicate assignments
        $assignmentCountBefore = EmployeeAssignment::where('employee_id', $employee->id)->count();
        $reExecuted = $executionService->execute($executed, $user);
        $this->assertEquals(PersonnelActionStatus::EXECUTED->value, $reExecuted->status);
        $this->assertEquals($assignmentCountBefore, EmployeeAssignment::where('employee_id', $employee->id)->count());
    }
}
