<?php

declare(strict_types=1);

namespace Tests\Feature\Operations;

use App\Domains\Operations\Models\OpsAlert;
use App\Domains\Operations\Models\OpsAlertRule;
use App\Domains\Operations\Models\OpsIncident;
use App\Domains\Operations\Models\OpsMetric;
use App\Domains\Operations\Services\IncidentManagementService;
use App\Domains\Operations\Services\OperationalTelemetryService;
use App\Domains\Platform\Services\HealthCheckService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ObservabilityAndReliabilityTest extends TestCase
{
    use RefreshDatabase;

    protected OperationalTelemetryService $telemetryService;
    protected IncidentManagementService $incidentService;
    protected HealthCheckService $healthService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->telemetryService = app(OperationalTelemetryService::class);
        $this->incidentService = app(IncidentManagementService::class);
        $this->healthService = app(HealthCheckService::class);
    }

    /**
     * 1. Health Probe Verification
     */
    public function test_liveness_probe_returns_ok(): void
    {
        $response = $this->getJson('/health/live');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'ok',
        ]);
        $this->assertArrayHasKey('uptime_seconds', $response->json());
        $this->assertArrayHasKey('timestamp', $response->json());
    }

    public function test_readiness_probe_returns_structured_status(): void
    {
        $response = $this->getJson('/health/ready');

        $this->assertContains($response->status(), [200, 503]);
        $json = $response->json();
        $this->assertArrayHasKey('status', $json);
        $this->assertArrayHasKey('ready', $json);
        $this->assertArrayHasKey('checks', $json);
        $this->assertArrayHasKey('database', $json['checks']);
        $this->assertArrayHasKey('storage', $json['checks']);
    }

    public function test_detailed_health_probe_returns_full_system_context(): void
    {
        $response = $this->getJson('/health');

        $this->assertContains($response->status(), [200, 503]);
        $json = $response->json();
        $this->assertArrayHasKey('application', $json);
        $this->assertArrayHasKey('version', $json);
        $this->assertArrayHasKey('environment', $json);
        $this->assertArrayHasKey('system', $json);
        $this->assertArrayHasKey('checks', $json);
        $this->assertArrayHasKey('database', $json['checks']);
        $this->assertArrayHasKey('cache', $json['checks']);
        $this->assertArrayHasKey('queue', $json['checks']);
        $this->assertArrayHasKey('storage', $json['checks']);
    }

    public function test_dependencies_health_probe_returns_infrastructure_tiers(): void
    {
        $response = $this->getJson('/health/dependencies');

        $this->assertContains($response->status(), [200, 503]);
        $json = $response->json();
        $this->assertArrayHasKey('status', $json);
        $this->assertArrayHasKey('healthy', $json);
        $this->assertArrayHasKey('dependencies', $json);
        $deps = $json['dependencies'];
        $this->assertArrayHasKey('database', $deps);
        $this->assertArrayHasKey('cache', $deps);
        $this->assertArrayHasKey('queue', $deps);
        $this->assertArrayHasKey('storage', $deps);
        $this->assertArrayHasKey('reverb', $deps);
        $this->assertArrayHasKey('ai', $deps);
        $this->assertArrayHasKey('mail', $deps);
        $this->assertArrayHasKey('webhooks', $deps);
    }

    public function test_services_health_probe_returns_all_domain_modules(): void
    {
        $response = $this->getJson('/health/services');

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertEquals('ok', $json['status']);
        $this->assertArrayHasKey('services', $json);
        $services = $json['services'];

        $expectedModules = [
            'identity_auth',
            'multi_tenancy',
            'hcm_core',
            'payroll_engine',
            'time_attendance',
            'performance',
            'recruitment',
            'workflow_engine',
            'analytics_engine',
            'notifications_hub',
            'integration_hub',
            'document_storage',
            'billing_engine',
            'security_audit',
        ];

        foreach ($expectedModules as $module) {
            $this->assertArrayHasKey($module, $services, "Missing domain health check for {$module}");
            $this->assertEquals('ok', $services[$module]['status']);
            $this->assertTrue($services[$module]['healthy']);
        }
    }

    /**
     * 2. Correlation ID & Distributed Tracing
     */
    public function test_request_propagates_custom_correlation_id_headers(): void
    {
        $customRequestId = 'req_custom_' . Str::uuid()->toString();
        $customCorrelationId = 'corr_custom_' . Str::uuid()->toString();

        $response = $this->withHeaders([
            'X-Request-ID' => $customRequestId,
            'X-Correlation-ID' => $customCorrelationId,
        ])->getJson('/health/live');

        $response->assertStatus(200);
        $response->assertHeader('X-Request-ID', $customRequestId);
        $response->assertHeader('X-Correlation-ID', $customCorrelationId);
    }

    public function test_request_generates_correlation_id_if_omitted(): void
    {
        $response = $this->getJson('/health/live');

        $response->assertStatus(200);
        $requestId = $response->headers->get('X-Request-ID');
        $correlationId = $response->headers->get('X-Correlation-ID');

        $this->assertNotNull($requestId);
        $this->assertNotNull($correlationId);
        $this->assertStringStartsWith('req_', $requestId);
    }

    /**
     * 3. Operational Telemetry & Alert Rule Evaluation
     */
    public function test_telemetry_service_records_metrics(): void
    {
        $tenantId = (string) Str::uuid();

        $metric = $this->telemetryService->recordMetric(
            metric: 'http_p95_latency_ms',
            value: 124.5,
            tenantId: $tenantId,
            unit: 'ms',
            dimensions: ['endpoint' => '/api/payroll/calculate', 'method' => 'POST']
        );

        $this->assertInstanceOf(OpsMetric::class, $metric);
        $this->assertEquals('http_p95_latency_ms', $metric->metric);
        $this->assertEquals(124.5, $metric->value);
        $this->assertEquals($tenantId, $metric->tenant_id);
        $this->assertEquals('ms', $metric->unit);
        $this->assertEquals('/api/payroll/calculate', $metric->dimensions['endpoint']);

        $this->assertDatabaseHas('ops_metrics', [
            'id' => $metric->id,
            'metric' => 'http_p95_latency_ms',
        ]);
    }

    public function test_alert_rule_evaluation_triggers_alert_when_threshold_breached(): void
    {
        $tenantId = (string) Str::uuid();

        // 1. Create active alert rule: db_query_latency_ms > 100
        $rule = OpsAlertRule::query()->create([
            'tenant_id' => $tenantId,
            'name' => 'High Database Query Latency',
            'metric' => 'db_query_latency_ms',
            'operator' => '>',
            'threshold' => 100.0,
            'severity' => 'critical',
            'channels' => ['slack', 'pagerduty'],
            'is_active' => true,
        ]);

        // 2. Record metric breaching threshold: value = 245.0
        $this->telemetryService->recordMetric(
            metric: 'db_query_latency_ms',
            value: 245.0,
            tenantId: $tenantId,
            unit: 'ms'
        );

        // 3. Evaluate alerts
        $alerts = $this->telemetryService->evaluateAlerts($tenantId);

        $this->assertCount(1, $alerts);
        $alert = $alerts->first();
        $this->assertInstanceOf(OpsAlert::class, $alert);
        $this->assertEquals('critical', $alert->severity);
        $this->assertEquals('open', $alert->status);
        $this->assertEquals(245.0, $alert->payload['current_value']);
        $this->assertEquals(100.0, $alert->payload['threshold']);

        $this->assertDatabaseHas('ops_alerts', [
            'id' => $alert->id,
            'rule_id' => $rule->id,
            'status' => 'open',
        ]);
    }

    public function test_alert_rule_does_not_trigger_when_within_safe_threshold(): void
    {
        $tenantId = (string) Str::uuid();

        OpsAlertRule::query()->create([
            'tenant_id' => $tenantId,
            'name' => 'High Memory Usage',
            'metric' => 'memory_usage_mb',
            'operator' => '>',
            'threshold' => 512.0,
            'severity' => 'warning',
            'channels' => ['slack'],
            'is_active' => true,
        ]);

        // Safe metric: 256.0 < 512.0
        $this->telemetryService->recordMetric(
            metric: 'memory_usage_mb',
            value: 256.0,
            tenantId: $tenantId,
            unit: 'MB'
        );

        $alerts = $this->telemetryService->evaluateAlerts($tenantId);
        $this->assertCount(0, $alerts);
    }

    /**
     * 4. Incident Management Lifecycle & Timeline
     */
    public function test_incident_lifecycle_creation_and_resolution(): void
    {
        $tenantId = (string) Str::uuid();

        // 1. Declare incident
        $incident = $this->incidentService->createIncident(
            title: 'Primary Database Deadlock Influx',
            severity: 'critical',
            description: 'Mass payroll run caused row-level lock contention on attendance punches',
            tenantId: $tenantId,
            actor: 'oncall-sre'
        );

        $this->assertInstanceOf(OpsIncident::class, $incident);
        $this->assertEquals('open', $incident->status);
        $this->assertEquals('critical', $incident->severity);
        $this->assertCount(1, $incident->timeline);
        $this->assertEquals('incident_created', $incident->timeline[0]['event']);

        // 2. Add investigation timeline event
        $this->incidentService->addTimelineEvent(
            $incident,
            event: 'lock_identified',
            actor: 'db-engineer',
            note: 'Identified long-running query thread 9481 holding locks'
        );
        $incident->refresh();
        $this->assertCount(2, $incident->timeline);

        // 3. Transition status
        $this->incidentService->updateStatus($incident, 'mitigating', actor: 'oncall-sre');
        $incident->refresh();
        $this->assertEquals('mitigating', $incident->status);
        $this->assertCount(3, $incident->timeline);

        // 4. Resolve incident
        $this->incidentService->resolveIncident(
            $incident,
            rootCause: 'Missing composite index on attendance_punches (tenant_id, punch_time)',
            actor: 'incident-commander'
        );
        $incident->refresh();
        $this->assertEquals('resolved', $incident->status);
        $this->assertNotNull($incident->resolved_at);
        $this->assertStringContainsString('Missing composite index', $incident->root_cause);
        $this->assertCount(4, $incident->timeline);
    }

    /**
     * 5. Queue & Dead-Letter Queue Operations
     */
    public function test_queue_health_summary_returns_driver_and_counts(): void
    {
        $summary = $this->telemetryService->getQueueHealthSummary();

        $this->assertArrayHasKey('driver', $summary);
        $this->assertArrayHasKey('pending_jobs', $summary);
        $this->assertArrayHasKey('failed_jobs', $summary);
        $this->assertArrayHasKey('status', $summary);
        $this->assertContains($summary['status'], ['healthy', 'degraded']);
    }

    /**
     * 6. Multi-Tenant Operational Isolation
     */
    public function test_tenant_operational_alerts_are_isolated(): void
    {
        $tenantA = (string) Str::uuid();
        $tenantB = (string) Str::uuid();

        // Alert for Tenant A
        $alertA = OpsAlert::query()->create([
            'tenant_id' => $tenantA,
            'severity' => 'warning',
            'status' => 'open',
            'message' => 'Tenant A sync latency degraded',
            'payload' => ['latency' => 300],
            'triggered_at' => now(),
        ]);

        // Alert for Tenant B
        $alertB = OpsAlert::query()->create([
            'tenant_id' => $tenantB,
            'severity' => 'critical',
            'status' => 'open',
            'message' => 'Tenant B webhook endpoint rejected',
            'payload' => ['status_code' => 500],
            'triggered_at' => now(),
        ]);

        // Query scoped to Tenant A
        $tenantAAlerts = OpsAlert::query()->where('tenant_id', $tenantA)->get();
        $this->assertTrue($tenantAAlerts->contains('id', $alertA->id));
        $this->assertFalse($tenantAAlerts->contains('id', $alertB->id));

        // Query scoped to Tenant B
        $tenantBAlerts = OpsAlert::query()->where('tenant_id', $tenantB)->get();
        $this->assertTrue($tenantBAlerts->contains('id', $alertB->id));
        $this->assertFalse($tenantBAlerts->contains('id', $alertA->id));
    }
}
