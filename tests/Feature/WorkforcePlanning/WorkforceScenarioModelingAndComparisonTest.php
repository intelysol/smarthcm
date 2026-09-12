<?php

namespace Tests\Feature\WorkforcePlanning;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforcePlanning\Enums\WorkforceScenarioType;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanService;
use App\Domains\WorkforcePlanning\Services\WorkforceScenarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceScenarioModelingAndComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_growth_and_cost_reduction_scenario_simulation_and_comparison(): void
    {
        $tenant = Tenant::factory()->create();
        $planService = app(WorkforcePlanService::class);
        $scenarioService = app(WorkforceScenarioService::class);

        $plan = $planService->createPlan([
            'tenant_id' => $tenant->id,
            'code' => 'WFP-SCEN-01',
            'name' => 'Scenario Baseline Plan',
            'planning_cycle' => 'FY2027',
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
        ]);

        // Create Growth Scenario
        $growthScenario = $scenarioService->createScenario($plan, [
            'name' => 'Aggressive Growth Plan',
            'scenario_type' => WorkforceScenarioType::GROWTH->value,
        ]);

        $growthResults = $scenarioService->simulateScenario($growthScenario);
        $this->assertEquals('calculated', $growthScenario->fresh()->status);
        $this->assertGreaterThan(0, $growthResults['projected_closing_headcount']);
        $this->assertGreaterThan(0, $growthResults['total_projected_cost']);

        // Create Cost Reduction Scenario
        $reductionScenario = $scenarioService->createScenario($plan, [
            'name' => 'Fiscal Restraint Plan',
            'scenario_type' => WorkforceScenarioType::COST_REDUCTION->value,
        ]);

        $reductionResults = $scenarioService->simulateScenario($reductionScenario);
        $this->assertLessThan($growthResults['total_projected_cost'], $reductionResults['total_projected_cost']);

        // Compare Scenarios
        $matrix = $scenarioService->compareScenarios([$growthScenario->id, $reductionScenario->id]);
        $this->assertCount(2, $matrix);
        $this->assertEquals('Aggressive Growth Plan', $matrix[0]['name']);
        $this->assertEquals('Fiscal Restraint Plan', $matrix[1]['name']);
    }
}
