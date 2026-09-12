<?php

namespace Tests\Feature\WorkforceProductivity;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceProductivity\Services\ProductivityScenarioService;
use App\Domains\WorkforceProductivity\Services\WorkforceROIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceRoiAndScenarioSimulationTest extends TestCase
{
    use RefreshDatabase;

    protected WorkforceROIService $roiService;
    protected ProductivityScenarioService $scenarioService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roiService = app(WorkforceROIService::class);
        $this->scenarioService = app(ProductivityScenarioService::class);
    }

    public function test_training_roi_calculation_with_explicit_correlation_label(): void
    {
        $tenant = Tenant::factory()->create();

        // Direct cost $10,000, 100 training hours at $30/hr wage, pre-rate 8 units/hr, post-rate 11 units/hr (delta +3), unit value $5, evaluated over 500 hours
        $calculation = $this->roiService->calculateTrainingRoi(
            tenantId: $tenant->id,
            trainingProgramName: 'CNC Precision Machining Masterclass',
            directTrainingCost: 10000.0,
            trainingHours: 100.0,
            hourlyWageRate: 30.0,
            preTrainingHourlyOutput: 8.0,
            postTrainingHourlyOutput: 11.0,
            unitValue: 5.0,
            evaluationPeriodHours: 500
        );

        // Total investment = 10,000 + (100 * 30) = $13,000
        $this->assertEquals(13000.0, $calculation->investment_cost);

        // Operational benefit = (11 - 8) * 500 * 5 = 3 * 500 * 5 = $7,500
        $this->assertEquals(7500.0, $calculation->operational_benefit);

        // Net benefit = 7,500 - 13,000 = -5,500
        $this->assertEquals(-5500.0, $calculation->net_benefit);
        $this->assertEquals(-42.31, $calculation->roi_percentage);

        // Causality must be labeled CORRELATION unless experimental control exists
        $this->assertEquals('CORRELATION', $calculation->causality_label);
    }

    public function test_recruitment_roi_evaluates_hiring_and_ramp_up_drag(): void
    {
        $tenant = Tenant::factory()->create();

        // Direct recruitment cost $15,000, 3 months ramp-up at 60% efficiency, $10,000 monthly output value
        $calculation = $this->roiService->calculateRecruitmentRoi(
            tenantId: $tenant->id,
            roleName: 'Senior Site Reliability Engineer',
            recruitmentCost: 15000.0,
            rampUpMonths: 3.0,
            standardMonthlyOutputValue: 10000.0,
            rampUpEfficiencyPct: 60.0
        );

        // Ramp loss = 3 * 10,000 * 40% = $12,000. Total investment = 15,000 + 12,000 = $27,000
        $this->assertEquals(27000.0, $calculation->investment_cost);
        // Annual output = 12 * 10,000 = $120,000
        $this->assertEquals(120000.0, $calculation->operational_benefit);
        // Net benefit = 120,000 - 27,000 = $93,000
        $this->assertEquals(93000.0, $calculation->net_benefit);
        $this->assertEquals(344.44, $calculation->roi_percentage);
    }

    public function test_what_if_workforce_scenario_simulation(): void
    {
        $tenant = Tenant::factory()->create();

        // Scenario: Hire +5 customer support agents at $5,000/mo, 160 hrs/mo each, output rate 10 tickets/hr, value $5/ticket
        $scenario = $this->scenarioService->simulateScenario(
            tenantId: $tenant->id,
            scenarioName: 'Peak Season Scale-Up',
            scenarioType: 'headcount_change',
            baselineSnapshotId: null,
            headcountDelta: 5,
            avgCostPerHead: 5000.0,
            avgMonthlyHoursPerHead: 160.0,
            baselineHourlyOutputRate: 10.0,
            valuePerUnit: 5.0
        );

        // FTE delta = 5, Capacity delta = 5 * 160 = 800 hours
        $this->assertEquals(5.0, $scenario->fte_delta);
        $this->assertEquals(800.0, $scenario->capacity_hours_delta);

        // Expected output delta = 800 * 10 = 8,000 units
        $this->assertEquals(8000.0, $scenario->expected_output_delta);

        // Cost delta = 5 * 5,000 = $25,000
        $this->assertEquals(25000.0, $scenario->cost_delta);

        // Projected unit cost = 25,000 / 8,000 = $3.125
        $this->assertEquals(3.125, $scenario->projected_cost_per_unit);

        // Gross benefit = 8,000 * 5 = $40,000. Net benefit = 40,000 - 25,000 = $15,000. ROI = 15,000 / 25,000 * 100 = 60%
        $this->assertEquals(60.0, $scenario->projected_roi_pct);
    }
}
