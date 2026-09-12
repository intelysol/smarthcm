<?php

namespace Tests\Feature\WorkforceProductivity;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceProductivity\DTOs\ProductivityMeasurementData;
use App\Domains\WorkforceProductivity\Models\HcmProductivityMeasurement;
use App\Domains\WorkforceProductivity\Services\AdvisoryWorkforceProductivityAiService;
use App\Domains\WorkforceProductivity\Services\ProductivityDataQualityService;
use App\Domains\WorkforceProductivity\Services\ProductivityMeasurementService;
use App\Domains\WorkforceProductivity\Services\ProductivityMetricService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductivitySecurityPrivacyAndAiGuardrailsTest extends TestCase
{
    use RefreshDatabase;

    protected ProductivityMeasurementService $measurementService;
    protected ProductivityMetricService $metricService;
    protected ProductivityDataQualityService $qualityService;
    protected AdvisoryWorkforceProductivityAiService $aiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->measurementService = app(ProductivityMeasurementService::class);
        $this->metricService = app(ProductivityMetricService::class);
        $this->qualityService = app(ProductivityDataQualityService::class);
        $this->aiService = app(AdvisoryWorkforceProductivityAiService::class);
    }

    public function test_multi_tenant_isolation_is_strictly_enforced(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $metricA = $this->metricService->createMetric([
            'tenant_id' => $tenantA->id,
            'code' => 'METRIC_A',
            'name' => 'Metric for Tenant A',
        ]);

        $metricB = $this->metricService->createMetric([
            'tenant_id' => $tenantB->id,
            'code' => 'METRIC_B',
            'name' => 'Metric for Tenant B',
        ]);

        // Querying Tenant A metrics should never return Tenant B metrics
        $metricsA = $this->metricService->listMetrics($tenantA->id);
        $this->assertTrue($metricsA->contains('id', $metricA->id));
        $this->assertFalse($metricsA->contains('id', $metricB->id));

        // Ingest measurement for Tenant A
        $dtoA = new ProductivityMeasurementData(
            tenantId: $tenantA->id,
            metricDefinitionId: $metricA->id,
            metricVersionId: $metricA->currentVersion->id,
            departmentId: null,
            locationId: null,
            shiftId: null,
            employeeId: null,
            periodType: 'monthly',
            periodName: '2026-10',
            periodStart: '2026-10-01',
            periodEnd: '2026-10-31',
            outputVolume: 1000.0,
            laborHours: 100.0,
            productiveHours: 100.0
        );
        $measurementA = $this->measurementService->calculateMeasurement($dtoA);

        $tenantBMeasurements = HcmProductivityMeasurement::where('tenant_id', $tenantB->id)->get();
        $this->assertFalse($tenantBMeasurements->contains('id', $measurementA->id));
    }

    public function test_ai_service_strictly_enforces_advisory_guardrails_and_prohibits_disciplinary_actions(): void
    {
        $tenant = Tenant::factory()->create();
        $insights = $this->aiService->generateInsights($tenant->id);

        $this->assertArrayHasKey('governance', $insights);
        $this->assertTrue($insights['governance']['is_advisory_only']);
        $this->assertFalse($insights['governance']['autonomous_actions_permitted']);
        $this->assertTrue($insights['governance']['surveillance_telemetry_prohibited']);
        $this->assertTrue($insights['governance']['disciplinary_action_prohibited']);
    }

    public function test_anomaly_detection_flags_severe_productivity_drops(): void
    {
        $tenant = Tenant::factory()->create();

        // Observed 6.0 units/hr vs Baseline 10.0 units/hr (-40% drop)
        $anomaly = $this->qualityService->detectAnomalies(
            tenantId: $tenant->id,
            departmentId: null,
            observedRate: 6.0,
            baselineRate: 10.0,
            detectedDate: '2026-10-20'
        );

        $this->assertNotNull($anomaly);
        $this->assertEquals('drop_spike', $anomaly->anomaly_type);
        $this->assertEquals('critical', $anomaly->severity);
        $this->assertEquals(-40.0, $anomaly->variance_pct);
        $this->assertDatabaseHas('hcm_productivity_anomalies', [
            'id' => $anomaly->id,
            'tenant_id' => $tenant->id,
            'status' => 'open',
        ]);
    }

    public function test_rest_api_endpoints_respond_with_json(): void
    {
        $tenant = Tenant::factory()->create();

        // 1. GET /api/v1/hcm/productivity/summary
        $responseSummary = $this->withHeader('X-Tenant-ID', $tenant->id)
            ->getJson('/api/v1/hcm/productivity/summary');
        $responseSummary->assertOk()
            ->assertJsonStructure(['snapshot', 'recent_measurements', 'scorecards']);

        // 2. GET /api/v1/hcm/productivity/metrics
        $responseMetrics = $this->withHeader('X-Tenant-ID', $tenant->id)
            ->getJson('/api/v1/hcm/productivity/metrics');
        $responseMetrics->assertOk()
            ->assertJsonStructure(['data']);

        // 3. GET /api/v1/hcm/productivity/labor-efficiency
        $responseEfficiency = $this->getJson('/api/v1/hcm/productivity/labor-efficiency?' . http_build_query([
            'output_volume' => 1000,
            'total_labor_hours' => 100,
            'productive_hours' => 80,
            'paid_hours' => 100,
            'overtime_hours' => 10,
            'total_fte' => 5,
            'total_workforce_cost' => 5000,
        ]));
        $responseEfficiency->assertOk()
            ->assertJsonPath('data.output_per_labor_hour', 10)
            ->assertJsonPath('data.labor_cost_per_unit', 5);

        // 4. GET /api/v1/hcm/productivity/ai/insights
        $responseAi = $this->withHeader('X-Tenant-ID', $tenant->id)
            ->getJson('/api/v1/hcm/productivity/ai/insights');
        $responseAi->assertOk()
            ->assertJsonPath('governance.is_advisory_only', true);
    }
}
