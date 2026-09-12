<?php

namespace Tests\Feature\WorkforceProductivity;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceProductivity\Contracts\ProductivityCalculationInterface;
use App\Domains\WorkforceProductivity\Models\HcmProductivityMetricDefinition;
use App\Domains\WorkforceProductivity\Services\ProductivityCalculationService;
use App\Domains\WorkforceProductivity\Services\ProductivityMetricService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductivityMetricAndVersioningTest extends TestCase
{
    use RefreshDatabase;

    protected ProductivityMetricService $metricService;
    protected ProductivityCalculationInterface $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = app(ProductivityCalculationService::class);
        $this->metricService = app(ProductivityMetricService::class);
    }

    public function test_tenant_can_create_metric_definition_with_initial_version(): void
    {
        $tenant = Tenant::factory()->create();

        $metric = $this->metricService->createMetric([
            'tenant_id' => $tenant->id,
            'code' => 'CASES_PER_HOUR',
            'name' => 'Resolved Cases per Productive Hour',
            'metric_type' => 'volume',
            'unit' => 'cases_per_hour',
            'numerator_code' => 'RESOLVED_CASES',
            'denominator_code' => 'PRODUCTIVE_HOURS',
        ]);

        $this->assertDatabaseHas('hcm_productivity_metric_definitions', [
            'id' => $metric->id,
            'tenant_id' => $tenant->id,
            'code' => 'CASES_PER_HOUR',
            'current_version' => 1,
        ]);

        $this->assertDatabaseHas('hcm_productivity_metric_versions', [
            'metric_definition_id' => $metric->id,
            'version' => 1,
            'is_current' => true,
        ]);
    }

    public function test_metric_formula_versioning_preserves_historical_immutability(): void
    {
        $tenant = Tenant::factory()->create();

        $metric = $this->metricService->createMetric([
            'tenant_id' => $tenant->id,
            'code' => 'UNITS_PER_HOUR',
            'name' => 'Units Produced per Hour',
            'numerator_code' => 'COMPLETED_UNITS',
            'denominator_code' => 'PRODUCTIVE_HOURS',
        ]);

        $v2 = $this->metricService->createNewVersion($metric->id, [
            'formula_name' => 'Weighted Units v2',
            'numerator_code' => 'WEIGHTED_COMPLETED_UNITS',
            'denominator_code' => 'ACTIVE_WORK_HOURS',
            'calculation_expression' => 'weighted_units / active_work_hours',
            'change_notes' => 'Adjusted for product complexity weights',
        ]);

        $metric->refresh();
        $this->assertEquals(2, $metric->current_version);

        // Version 1 should now be inactive and expired
        $v1 = $metric->versions()->where('version', 1)->first();
        $this->assertFalse((bool) $v1->is_current);
        $this->assertNotNull($v1->effective_to);

        // Version 2 should be active
        $this->assertTrue((bool) $v2->is_current);
        $this->assertNull($v2->effective_to);
    }

    public function test_zero_denominator_safely_returns_null_and_never_divides_by_zero(): void
    {
        // 1. Zero hours should return null (N/A)
        $rateZeroHours = $this->calculator->calculateRate(100.0, 0.0);
        $this->assertNull($rateZeroHours);

        // 2. Negative hours should return null
        $rateNegativeHours = $this->calculator->calculateRate(100.0, -5.0);
        $this->assertNull($rateNegativeHours);

        // 3. Null output should return null
        $rateNullOutput = $this->calculator->calculateRate(null, 40.0);
        $this->assertNull($rateNullOutput);

        // 4. Valid hours and output should compute accurate rate
        $validRate = $this->calculator->calculateRate(250.0, 25.0);
        $this->assertEquals(10.0, $validRate);

        // 5. Unit cost zero output should return null
        $unitCostZeroOutput = $this->calculator->calculateUnitCost(500.0, 0.0);
        $this->assertNull($unitCostZeroOutput);

        // 6. Valid unit cost
        $validUnitCost = $this->calculator->calculateUnitCost(500.0, 250.0);
        $this->assertEquals(2.0, $validUnitCost);
    }
}
