<?php

namespace Tests\Feature\WorkforcePlanning;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforcePlanning\Services\WorkforceDemandSupplyService;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceDemandSupplyAndGapTest extends TestCase
{
    use RefreshDatabase;

    public function test_demand_supply_and_workforce_gap_analysis(): void
    {
        $tenant = Tenant::factory()->create();
        $planService = app(WorkforcePlanService::class);
        $demandSupplyService = app(WorkforceDemandSupplyService::class);

        $plan = $planService->createPlan([
            'tenant_id' => $tenant->id,
            'code' => 'WFP-GAP-01',
            'name' => 'Demand Supply Plan',
            'planning_cycle' => 'FY2027',
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
        ]);

        // Record Demand: Current 100, Required 130 -> Gap +30 FTE
        $demand = $demandSupplyService->recordDemandPlan($plan, [
            'driver_name' => 'revenue_growth',
            'current_fte' => 100.00,
            'required_fte' => 130.00,
        ]);

        $this->assertEquals(30.00, (float) $demand->demand_gap_fte);

        // Record Supply: Current 100, Retirements 5, Attrition 10, Transfers Out 2, External Hiring 25 -> Closing 108
        $supply = $demandSupplyService->recordSupplyPlan($plan, [
            'current_headcount' => 100,
            'expected_retirements' => 5,
            'expected_attrition' => 10,
            'expected_transfers_out' => 2,
            'expected_transfers_in' => 0,
            'expected_internal_hires' => 0,
            'external_hiring_required' => 25,
        ]);

        $this->assertEquals(108, $supply->projected_closing_headcount);

        // Overall Gap Analysis
        $gap = $demandSupplyService->getWorkforceGapAnalysis($tenant->id, $plan->id);
        $this->assertEquals(130.00, (float) $gap['total_demand_fte']);
        $this->assertEquals(108, $gap['total_projected_supply']);
        // Net gap: 130 - 108 = 22 FTE
        $this->assertEquals(22.00, (float) $gap['net_workforce_gap']);
    }
}
