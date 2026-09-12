<?php

namespace Tests\Unit\Learning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningRequirement;
use App\Domains\Learning\Models\LearningRequirementAssignment;
use App\Domains\Learning\Services\LearningRequirementService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningRequirementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_mandatory_requirement_assignment_to_matching_department(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'code' => 'BU-FIN',
            'name' => 'Finance BU',
        ]);
        $department = Department::query()->create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_name' => 'Finance',
            'department_code' => 'FIN',
        ]);

        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $department->id,
            'employee_number' => 'EMP-030',
            'employee_code' => 'EMP-030',
            'first_name' => 'Fatima',
            'last_name' => 'Noor',
            'joining_date' => now()->toDateString(),
        ]);

        $course = LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'CRS-FIN-1',
            'title' => 'Financial Fraud Prevention',
            'delivery_type' => 'self_paced',
            'duration' => 2,
        ]);

        $requirement = LearningRequirement::query()->create([
            'tenant_id' => $tenant->id,
            'course_id' => $course->id,
            'title' => 'Annual Finance Compliance',
            'target_type' => 'department',
            'target_id' => $department->id,
            'deadline_type' => 'relative_hiring',
            'days_offset' => 30,
            'status' => 'active',
        ]);

        $service = app(LearningRequirementService::class);
        $assignments = $service->evaluateRequirementsForEmployee($employee, 'hired');

        $this->assertCount(1, $assignments);
        $this->assertEquals($requirement->id, $assignments[0]->requirement_id);
        $this->assertEquals($employee->id, $assignments[0]->employee_id);
        $this->assertEquals('assigned', $assignments[0]->status);
    }

    public function test_requirement_waiver_workflow(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-031',
            'employee_code' => 'EMP-031',
            'first_name' => 'Kashif',
            'last_name' => 'Iqbal',
            'joining_date' => now()->toDateString(),
        ]);

        $course = LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'CRS-GEN-1',
            'title' => 'Ethics & Code of Conduct',
            'delivery_type' => 'self_paced',
            'duration' => 1,
        ]);

        $requirement = LearningRequirement::query()->create([
            'tenant_id' => $tenant->id,
            'course_id' => $course->id,
            'title' => 'General Ethics',
            'target_type' => 'company',
            'target_id' => $company->id,
            'deadline_type' => 'absolute',
            'due_date' => now()->addDays(15),
            'status' => 'active',
        ]);

        $assignment = LearningRequirementAssignment::query()->create([
            'tenant_id' => $tenant->id,
            'requirement_id' => $requirement->id,
            'employee_id' => $employee->id,
            'course_id' => $course->id,
            'status' => 'assigned',
            'assigned_at' => now(),
            'due_at' => now()->addDays(15),
        ]);

        $service = app(LearningRequirementService::class);
        $waived = $service->waiveRequirement($user, $assignment, 'Prior certification verified.');

        $this->assertEquals('waived', $waived->status);
        $this->assertEquals('Prior certification verified.', $waived->waiver_reason);
        $this->assertEquals($user->id, $waived->waived_by);
    }
}
