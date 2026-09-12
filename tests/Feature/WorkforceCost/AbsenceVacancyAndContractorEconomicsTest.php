<?php

namespace Tests\Feature\WorkforceCost;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceCost\Services\LaborCostAggregationService;
use App\Domains\WorkforceCost\Services\WorkforceCostScenarioService;
use App\Domains\WorkforceCost\Services\WorkforceEconomicsService;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsenceVacancyAndContractorEconomicsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_absence_vacancy_and_contractor_scenario_economics(): void
    {
        $tenant = Tenant::factory()->create();
        $aggService = app(LaborCostAggregationService::class);
        $econService = app(WorkforceEconomicsService::class);
        $scenarioService = app(WorkforceCostScenarioService::class);

        // 1. Ingest absence cost & vacancy cost lines
        $aggService->createCostLine($tenant->id, [
            'cost_category' => 'opportunity',
            'component_type' => 'ABSENCE',
            'cost_date' => '2026-10-10',
            'amount' => 3500.00,
        ]);

        $aggService->createCostLine($tenant->id, [
            'cost_category' => 'opportunity',
            'component_type' => 'VACANCY',
            'cost_date' => '2026-10-12',
            'amount' => 8000.00,
        ]);

        $aggService->createCostLine($tenant->id, [
            'cost_category' => 'contractor',
            'component_type' => 'CONTRACTOR',
            'cost_date' => '2026-10-15',
            'amount' => 15000.00,
        ]);

        $economics = $econService->calculateEconomics(
            $tenant->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-10-31')
        );

        $this->assertEquals(3500.00, (float) $economics->absence_cost_total);
        $this->assertEquals(8000.00, (float) $economics->vacancy_cost_total);

        // 2. Scenario: Contractor Substitution
        // Contractor rate $75/hr vs Employee $50/hr for 2000 hours -> diff = $50,000
        $scenario = $scenarioService->calculateScenario(
            $tenant->id,
            'Contractor Conversion Analysis',
            'contractor_substitution',
            500000.00,
            [
                'contractor_hourly_rate' => 75.00,
                'employee_hourly_cost' => 50.00,
                'annual_hours' => 2000.00,
                'headcount_difference' => 1,
            ]
        );

        $this->assertNotNull($scenario);
        $this->assertEquals(50000.00, (float) $scenario->cost_difference);
        $this->assertEquals(550000.00, (float) $scenario->scenario_cost);

        // 3. Scenario: Automation ROI
        // $100,000 investment, $150,000 annual labor savings -> ROI = 50%, Payback = 8 months
        $autoScenario = $scenarioService->calculateScenario(
            $tenant->id,
            'Invoice Processing Automation',
            'automate',
            1000000.00,
            [
                'investment_cost' => 100000.00,
                'annual_labor_saving' => 150000.00,
            ]
        );

        $this->assertEquals(-150000.00, (float) $autoScenario->cost_difference);
        $this->assertEquals(50.00, (float) $autoScenario->estimated_roi_percentage);
        $this->assertEquals(8.0, (float) $autoScenario->payback_months);
    }
}