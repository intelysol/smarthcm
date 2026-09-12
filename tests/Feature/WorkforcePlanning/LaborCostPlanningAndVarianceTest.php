<?php

namespace Tests\Feature\WorkforcePlanning;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforcePlanning\Services\LaborCostPlanningService;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaborCostPlanningAndVarianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_labor_cost_recording_and_variance_calculation(): void
    {
        $tenant = Tenant::factory()->create();
        $planService = app(WorkforcePlanService::class);
        $costService = app(LaborCostPlanningService::class);

        $plan = $planService->createPlan([
            'tenant_id' => $tenant->id,
            'code' => 'WFP-COST-01',
            'name' => 'Cost Plan',
            'planning_cycle' => 'FY2027',
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
        ]);

        $costService->recordCostPlan($plan, [
            'cost_category' => 'base_salary',
            'budgeted_amount' => 5000000.00,
            'forecast_amount' => 4950000.00,
            'actual_amount' => 4900000.00,
        ]);

        $costService->recordCostPlan($plan, [
            'cost_category' => 'benefits',
            'budgeted_amount' => 800000.00,
            'forecast_amount' => 820000.00,
            'actual_amount' => 780000.00,
        ]);

        $totals = $costService->calculateTotalLaborCost($tenant->id, $plan->id);

        $this->assertEquals(5800000.00, (float) $totals['total_budgeted']);
        $this->assertEquals(5770000.00, (float) $totals['total_forecast']);
        $this->assertEquals(5680000.00, (float) $totals['total_actual']);
        // Variance: 5800000 - 5680000 = 120000.00
        $this->assertEquals(120000.00, (float) $totals['total_variance']);
    }
}
