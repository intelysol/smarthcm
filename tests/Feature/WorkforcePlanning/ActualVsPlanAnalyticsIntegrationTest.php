<?php

namespace Tests\Feature\WorkforcePlanning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforcePlanning\Services\ActualVsPlanAnalyticsService;
use App\Domains\WorkforcePlanning\Services\HeadcountPlanningService;
use App\Domains\WorkforcePlanning\Services\LaborCostPlanningService;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActualVsPlanAnalyticsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_actual_vs_plan_matrix_calculation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-01']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Operations', 'department_code' => 'OPS']);

        // Create 5 active employees in Core HR
        for ($i = 1; $i <= 5; $i++) {
            Employee::create([
                'tenant_id' => $tenant->id,
                'company_id' => $company->id,
                'department_id' => $dept->id,
                'employee_code' => "EMP-ACT-{$i}",
                'employee_number' => "EMP-ACT-{$i}",
                'first_name' => "Ops",
                'last_name' => "User {$i}",
                'official_email' => "ops{$i}@example.com",
                'employment_status' => 'active',
                'joining_date' => '2026-01-01',
            ]);
        }

        $planService = app(WorkforcePlanService::class);
        $hcService = app(HeadcountPlanningService::class);
        $costService = app(LaborCostPlanningService::class);
        $analyticsService = app(ActualVsPlanAnalyticsService::class);

        $plan = $planService->createPlan([
            'tenant_id' => $tenant->id,
            'code' => 'WFP-ACT-01',
            'name' => 'Actual vs Plan Integration',
            'planning_cycle' => 'FY2027',
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
        ]);

        $period = $plan->periods()->first();
        $hcService->recordHeadcountPeriod($plan, $period, [
            'department_id' => $dept->id,
            'opening_headcount' => 5,
            'planned_hires' => 3,
            'planned_exits' => 0,
            'closing_headcount' => 8,
        ]);

        $costService->recordCostPlan($plan, [
            'department_id' => $dept->id,
            'cost_category' => 'base_salary',
            'budgeted_amount' => 450000.00,
            'forecast_amount' => 430000.00,
            'actual_amount' => 420000.00,
        ]);

        $matrix = $analyticsService->getActualVsPlanMatrix($plan, $dept->id);

        $this->assertEquals(5, $matrix['headcount']['actual']);
        $this->assertEquals(8, $matrix['headcount']['planned']);
        // Variance: 5 - 8 = -3
        $this->assertEquals(-3, $matrix['headcount']['variance']);
        $this->assertArrayHasKey('labor_cost', $matrix);
    }
}
