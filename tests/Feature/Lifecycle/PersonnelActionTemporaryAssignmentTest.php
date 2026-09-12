<?php

namespace Tests\Feature\Lifecycle;

use App\Domains\Employee\Models\Employee;
use App\Domains\Lifecycle\Models\PersonnelTemporaryAssignment;
use App\Domains\Lifecycle\Services\PersonnelActionAssignmentService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelActionTemporaryAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_temporary_acting_assignment_lifecycle_and_reversion(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ', 'code' => 'BU-TA']);
        $deptHome = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Operations', 'department_code' => 'OPS-01']);
        $deptActing = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Executive Office', 'department_code' => 'EXEC-01']);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $deptHome->id,
            'employee_code' => 'EMP-TA-1',
            'employee_number' => 'EMP-TA-1',
            'first_name' => 'Toby',
            'last_name' => 'Flenderson',
            'official_email' => 'toby@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $service = new PersonnelActionAssignmentService();

        // 1. Create Acting Assignment
        $assignment = $service->createAssignment($employee, [
            'assignment_type' => 'acting',
            'temporary_department_id' => $deptActing->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'reason' => 'Interim Department Coverage',
        ]);

        $this->assertEquals('acting', $assignment->assignment_type);
        $this->assertEquals('active', $assignment->status);
        $this->assertEquals($deptHome->id, $assignment->home_department_id);
        $this->assertEquals($deptActing->id, $assignment->temporary_department_id);

        // Update employee to acting department
        $employee->update(['department_id' => $deptActing->id]);
        $this->assertEquals($deptActing->id, $employee->fresh()->department_id);

        // 2. Complete and Revert Assignment
        $reverted = $service->completeAndRevertAssignment($assignment);
        $this->assertEquals('reverted', $reverted->status);
        $this->assertEquals($deptHome->id, $employee->fresh()->department_id);
    }
}
