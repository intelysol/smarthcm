<?php

namespace Tests\Feature\WorkforcePlanning;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Services\HeadcountPlanningService;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeadcountPlanningDeterministicCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_deterministic_headcount_formula_calculation(): void
    {
        $service = app(HeadcountPlanningService::class);

        // Formula: Closing = Opening + Hires + Transfers In - Exits - Transfers Out
        // 1000 + 50 + 10 - 20 - 5 = 1035
        $closing = $service->calculateDeterministicBalance(1000, 50, 10, 20, 5);
        $this->assertEquals(1035, $closing);

        // Zero floor check (cannot be negative)
        $negativeTest = $service->calculateDeterministicBalance(10, 0, 0, 50, 0);
        $this->assertEquals(0, $negativeTest);
    }

    public function test_recording_monthly_headcount_plan_period(): void
    {
        $tenant = Tenant::factory()->create();
        $planService = app(WorkforcePlanService::class);
        $hcService = app(HeadcountPlanningService::class);

        $plan = $planService->createPlan([
            'tenant_id' => $tenant->id,
            'code' => 'WFP-HC-01',
            'name' => 'Headcount Test Plan',
            'planning_cycle' => 'FY2027',
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
        ]);

        $period = $plan->periods()->first();

        $record = $hcService->recordHeadcountPeriod($plan, $period, [
            'opening_headcount' => 100,
            'planned_hires' => 15,
            'transfers_in' => 2,
            'planned_exits' => 5,
            'transfers_out' => 1,
            'planned_fte' => 111.00,
        ]);

        $this->assertEquals(111, $record->closing_headcount);
        $this->assertEquals(111.00, (float) $record->planned_fte);
    }
}
