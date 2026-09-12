<?php

namespace Tests\Feature\WorkforceOptimization;

use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceOptimization\Contracts\OptimizationSolverInterface;
use App\Domains\WorkforceOptimization\Contracts\WorkforceOptimizationInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiObjectiveScenarioComparisonTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();
    }

    public function test_can_simulate_and_compare_what_if_scenarios(): void
    {
        $company = \App\Domains\Organization\Models\Company::factory()->create(['tenant_id' => $this->tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'name' => 'Strategy BU', 'code' => 'STR']);
        $dept = Department::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $company->id,
            'department_name' => 'Operations Planning',
            'department_code' => 'OPS-PLAN',
            'business_unit_id' => $bu->id
        ]);

        $optimizationService = app(WorkforceOptimizationInterface::class);
        $run = $optimizationService->runOptimization($this->tenant->id, scope: ['department_id' => $dept->id]);

        // Simulate Scenario A (Hire permanent)
        $scenarioA = $optimizationService->simulateScenario(
            run: $run,
            scenarioCode: 'SCENARIO_PERM_HIRE',
            name: 'Permanent Staff Expansion',
            parameters: [
                'cost_delta' => 45000.0,
                'capacity_delta_hours' => 640.0,
                'productivity_delta_pct' => 15.0,
                'risk_score' => 30.0,
            ]
        );

        $this->assertDatabaseHas('hcm_workforce_optimization_scenarios', [
            'id' => $scenarioA->id,
            'scenario_name' => 'Permanent Staff Expansion',
        ]);

        $this->assertDatabaseHas('hcm_workforce_optimization_scenario_results', [
            'scenario_id' => $scenarioA->id,
            'option_label' => 'SCENARIO_PERM_HIRE',
        ]);

        // Simulate Scenario B (Contractor surge)
        $scenarioB = $optimizationService->simulateScenario(
            run: $run,
            scenarioCode: 'SCENARIO_CONTRACTORS',
            name: 'Contingent Labor Surge',
            parameters: [
                'cost_delta' => 60000.0,
                'capacity_delta_hours' => 600.0,
                'productivity_delta_pct' => 5.0,
                'risk_score' => 60.0,
            ]
        );

        // Compare scenarios
        $comparison = $optimizationService->compareScenarios([
            [
                'scenario_code' => 'SCENARIO_PERM_HIRE',
                'name' => 'Permanent Staff Expansion',
                'metrics' => [
                    'cost_impact' => 45000.0,
                    'capacity_impact_hours' => 640.0,
                    'productivity_impact_pct' => 15.0,
                    'risk_impact_score' => 30.0,
                    'decision_score' => 78.5,
                ],
            ],
            [
                'scenario_code' => 'SCENARIO_CONTRACTORS',
                'name' => 'Contingent Labor Surge',
                'metrics' => [
                    'cost_impact' => 60000.0,
                    'capacity_impact_hours' => 600.0,
                    'productivity_impact_pct' => 5.0,
                    'risk_impact_score' => 60.0,
                    'decision_score' => 42.0,
                ],
            ],
        ]);

        $this->assertEquals('SCENARIO_PERM_HIRE', $comparison->recommendedScenarioCode);
        $this->assertArrayHasKey('SCENARIO_PERM_HIRE', $comparison->comparedScenarios);
        $this->assertArrayHasKey('SCENARIO_CONTRACTORS', $comparison->comparedScenarios);
    }
}
