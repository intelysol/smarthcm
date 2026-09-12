<?php

namespace Tests\Feature\WorkforceProductivity;

use App\Domains\WorkforceProductivity\Services\LaborEfficiencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaborEfficiencyAndWorkforceCostIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected LaborEfficiencyService $efficiencyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->efficiencyService = app(LaborEfficiencyService::class);
    }

    public function test_labor_efficiency_computation_integrating_workforce_cost(): void
    {
        // Example: Output 50,000 units, Total hours 5,000, Productive 4,000, Paid 5,200, Overtime 400, FTE 30, Cost $125,000
        $data = $this->efficiencyService->computeEfficiency(
            outputVolume: 50000.0,
            totalLaborHours: 5000.0,
            productiveHours: 4000.0,
            paidHours: 5200.0,
            overtimeHours: 400.0,
            totalFte: 30.0,
            totalWorkforceCost: 125000.0
        );

        $this->assertEquals(10.0, $data->outputPerLaborHour); // 50,000 / 5,000
        $this->assertEquals(12.5, $data->outputPerProductiveHour); // 50,000 / 4,000
        $this->assertEquals(1666.67, $data->outputPerFte); // 50,000 / 30
        $this->assertEquals(76.92, $data->productiveToPaidRatio); // 4,000 / 5,200 * 100
        $this->assertEquals(8.0, $data->overtimeRatio); // 400 / 5,000 * 100
        $this->assertEquals(2.5, $data->laborCostPerUnit); // 125,000 / 50,000
        $this->assertEquals(31.25, $data->laborCostPerProductiveHour); // 125,000 / 4,000
        $this->assertEquals(0.4, $data->outputPerWorkforceDollar); // 50,000 / 125,000
    }
}
