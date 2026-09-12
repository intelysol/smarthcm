<?php

namespace Tests\Feature\WorkforceProductivity;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceProductivity\Services\ProductivityBenchmarkService;
use App\Domains\WorkforceProductivity\Services\ProductivityForecastService;
use App\Domains\WorkforceProductivity\Services\ProductivityVarianceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductivityForecastingAndBenchmarkingTest extends TestCase
{
    use RefreshDatabase;

    protected ProductivityForecastService $forecastService;
    protected ProductivityBenchmarkService $benchmarkService;
    protected ProductivityVarianceService $varianceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->forecastService = app(ProductivityForecastService::class);
        $this->benchmarkService = app(ProductivityBenchmarkService::class);
        $this->varianceService = app(ProductivityVarianceService::class);
    }

    public function test_productivity_forward_forecasting(): void
    {
        $tenant = Tenant::factory()->create();

        $forecast = $this->forecastService->generateForecast(
            tenantId: $tenant->id,
            departmentId: null,
            forecastStart: '2026-11-01',
            forecastEnd: '2026-11-30',
            assumptions: [
                'headcount_growth_pct' => 10.0,
                'automation_efficiency_pct' => 5.0,
                'wage_inflation_pct' => 2.0,
            ]
        );

        $this->assertDatabaseHas('hcm_productivity_forecasts', [
            'id' => $forecast->id,
            'tenant_id' => $tenant->id,
        ]);
        $this->assertNotNull($forecast->projected_output);
        $this->assertNotNull($forecast->projected_cost_per_unit);
        $this->assertGreaterThan(0, $forecast->projected_output);
    }

    public function test_internal_benchmarking_and_normalized_productivity_index(): void
    {
        $tenant = Tenant::factory()->create();

        // Baseline rate 10.0 units/hr (Index 100), Comparison rate 11.5 units/hr (Index 115)
        $benchmark = $this->benchmarkService->createBenchmark(
            tenantId: $tenant->id,
            benchmarkName: 'Q3 vs Q4 Shift A Productivity Benchmark',
            benchmarkType: 'shift',
            baseStart: '2026-07-01',
            baseEnd: '2026-09-30',
            baseRate: 10.0,
            compStart: '2026-10-01',
            compEnd: '2026-12-31',
            compRate: 11.5
        );

        $this->assertEquals(115.0, $benchmark->normalized_index); // (11.5 / 10.0) * 100
        $this->assertEquals(15.0, $benchmark->variance_pct); // +15%
    }

    public function test_productivity_variance_decomposition_into_volume_and_efficiency_drivers(): void
    {
        $baseline = [
            'output' => 1000.0,
            'hours' => 100.0,
            'cost' => 2000.0, // $2.00 / unit, 10.0 units/hr
        ];

        $comparison = [
            'output' => 1200.0,
            'hours' => 100.0,
            'cost' => 2100.0, // $1.75 / unit, 12.0 units/hr
        ];

        $variance = $this->varianceService->calculateVariance($baseline, $comparison);

        $this->assertEquals(200.0, $variance['variance']['output_delta']);
        $this->assertEquals(20.0, $variance['variance']['output_delta_pct']);
        $this->assertEquals(2.0, $variance['variance']['rate_delta']);
        $this->assertEquals(20.0, $variance['variance']['rate_delta_pct']);
        $this->assertEquals(-0.25, $variance['variance']['unit_cost_delta']); // Unit cost dropped from $2.00 to $1.75

        // Volume cost driver: 200 * $2.00 = +$400
        $this->assertEquals(400.0, $variance['drivers']['volume_cost_driver']);
        // Efficiency cost driver: Actual cost change ($100) - Volume driver ($400) = -$300 savings
        $this->assertEquals(-300.0, $variance['drivers']['efficiency_cost_driver']);
    }
}
