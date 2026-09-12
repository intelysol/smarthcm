<?php

namespace Tests\Feature\WorkforcePlanning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforcePlanning\Enums\HiringPriority;
use App\Domains\WorkforcePlanning\Enums\ReplacementType;
use App\Domains\WorkforcePlanning\Services\HiringPlanService;
use App\Domains\WorkforcePlanning\Services\PositionPlanningService;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HiringPlanAndReplacementHiringTest extends TestCase
{
    use RefreshDatabase;

    public function test_hiring_plan_requirement_and_recruitment_handoff(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-01']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Engineering', 'department_code' => 'ENG']);

        $exitingEmp = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-RET-01',
            'employee_number' => 'EMP-RET-01',
            'first_name' => 'Retiring',
            'last_name' => 'Staff',
            'official_email' => 'retire@example.com',
            'employment_status' => 'active',
            'joining_date' => '2015-01-01',
        ]);

        $planService = app(WorkforcePlanService::class);
        $positionService = app(PositionPlanningService::class);
        $hiringService = app(HiringPlanService::class);

        $plan = $planService->createPlan([
            'tenant_id' => $tenant->id,
            'code' => 'WFP-HIRE-01',
            'name' => 'Hiring Plan Test',
            'planning_cycle' => 'FY2027',
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
        ]);

        $position = $positionService->createPosition($plan, [
            'position_code' => 'POS-LEAD-01',
            'title' => 'Technical Lead',
            'department_id' => $dept->id,
        ]);

        $hiringPlan = $hiringService->createHiringRequirement($plan, [
            'position_plan_id' => $position->id,
            'title' => 'Technical Lead Replacement',
            'department_id' => $dept->id,
            'planned_start_date' => '2027-03-01',
            'priority' => HiringPriority::HIGH->value,
            'reason' => 'replacement',
            'replacement_type' => ReplacementType::IMMEDIATE->value,
            'replaces_employee_id' => $exitingEmp->id,
        ]);

        $this->assertEquals(HiringPriority::HIGH->value, $hiringPlan->priority);
        $this->assertEquals(ReplacementType::IMMEDIATE->value, $hiringPlan->replacement_type);
        $this->assertEquals($exitingEmp->id, $hiringPlan->replaces_employee_id);

        // Generate Recruitment Handoff Payload
        $payload = $hiringService->generateRecruitmentHandoffPayload($hiringPlan);
        $this->assertEquals('Technical Lead Replacement', $payload['title']);
        $this->assertEquals('ready_for_recruitment', $payload['status']);
        $this->assertEquals('2027-03-01', $payload['target_start_date']);
    }
}
