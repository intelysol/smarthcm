<?php

declare(strict_types=1);

namespace Tests\Feature\Integration;

use App\Domains\Employee\Models\Employee;
use App\Domains\Integration\Jobs\InboundSyncJob;
use App\Domains\Integration\Jobs\OutboundIntegrationJob;
use App\Domains\Integration\Models\IntegrationConnection;
use App\Domains\Integration\Models\IntegrationConnector;
use App\Domains\Integration\Models\IntegrationDeadLetter;
use App\Domains\Integration\Models\IntegrationMapping;
use App\Domains\Integration\Models\IntegrationSyncRun;
use App\Domains\Integration\Services\DeadLetterService;
use App\Domains\Integration\Services\IntegrationAiAssistant;
use App\Domains\Integration\Services\IntegrationMonitoringService;
use App\Domains\Integration\Services\SyncEngine;
use App\Domains\Integration\Services\WebhookSecurityService;
use App\Domains\Shared\Models\Tenant;
use Flow\Packages\Integrations\Infrastructure\Connectors\DemoHrConnector;
use Flow\Packages\Integrations\Infrastructure\Services\ConnectorRegistry;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class EndToEndDemoIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected Tenant $tenant;
    protected IntegrationConnector $connector;
    protected IntegrationConnection $connection;
    protected IntegrationMapping $mapping;
    protected string $webhookSecret = 'whsec_demo_hr_vault_key_9999';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'smarthcm',
        ]);

        // 1. Establish tenant
        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'test-integration-tenant'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Demo Integration Test Tenant',
                'tenant_code' => 'DEMO-INT-01',
                'status' => 'active',
            ]
        );

        // 2. Register & sync connectors
        /** @var ConnectorRegistry $registry */
        $registry = app(ConnectorRegistry::class);
        $registry->syncToDatabase($this->tenant->id);

        $this->connector = IntegrationConnector::where('key', 'demo-hr-provider')->firstOrFail();

        // 3. Establish tenant integration connection
        $this->connection = IntegrationConnection::create([
            'tenant_id' => $this->tenant->id,
            'connector_id' => $this->connector->id,
            'name' => 'Acme Demo HR Live Link',
            'status' => 'active',
            'configuration' => [
                'base_url' => 'https://mock-hr.external.org/api/v2',
            ],
            'encrypted_credentials' => [
                'api_key' => 'live_demo_hr_api_key_44332211',
                'webhook_secret' => $this->webhookSecret,
            ],
        ]);

        // 4. Configure integration mapping
        $this->mapping = IntegrationMapping::create([
            'connection_id' => $this->connection->id,
            'name' => 'Demo HR to Employee Mapping',
            'source_format' => 'json',
            'target_format' => 'json',
            'mapping' => [
                'field_mappings' => [
                    'first_name' => 'first_name',
                    'last_name' => 'last_name',
                    'official_email' => 'work_email',
                    'employment_status' => 'employment_status',
                    'hire_date' => 'hire_date',
                ],
                'transform_rules' => [
                    [
                        'when' => ['field' => 'employment_status', 'operator' => 'equals', 'value' => 'full_time'],
                        'set' => ['employment_status' => 'full_time'],
                    ]
                ],
            ],
            'validation_rules' => [],
        ]);
    }

    public function test_step_1_to_8_inbound_webhook_and_employee_lifecycle(): void
    {
        $security = app(WebhookSecurityService::class);
        $uniqueId = Str::lower(Str::random(6));
        $testEmail = "alexander.{$uniqueId}@demo-external.org";

        $payload = [
            'event_id' => 'EVT-TEST-' . strtoupper($uniqueId),
            'event_type' => 'employee.created',
            'idempotency_key' => 'IDEM-KEY-' . strtoupper($uniqueId),
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'first_name' => 'Alexander',
                'last_name' => 'Hamilton',
                'work_email' => $testEmail,
                'job_title' => 'Secretary of Treasury',
                'employment_status' => 'full_time',
                'hire_date' => '2026-01-15',
            ],
        ];

        $rawJson = json_encode($payload);
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $rawJson, $this->webhookSecret);

        // Send Inbound Webhook
        $response = $this->withHeaders([
            'X-SmartHCM-Signature' => 'sha256=' . $signature,
            'X-SmartHCM-Timestamp' => $timestamp,
            'X-Tenant-ID' => $this->tenant->id,
            'Content-Type' => 'application/json',
        ])->postJson('/api/v1/integrations/webhooks/demo-hr-provider', $payload);

        $response->assertStatus(202);
        $response->assertJsonPath('data.status', 'accepted');
        $response->assertJsonPath('data.processed', true);

        // Verify Employee record was created in database
        $employee = Employee::where('tenant_id', $this->tenant->id)
            ->where('official_email', $testEmail)
            ->first();

        $this->assertNotNull($employee, 'Employee must be created in database');
        $this->assertSame('Alexander', $employee->first_name);
        $this->assertSame('Hamilton', $employee->last_name);
    }

    public function test_step_9_duplicate_webhook_payload_suppressed_by_idempotency(): void
    {
        $uniqueId = Str::lower(Str::random(6));
        $testEmail = "duplicate.test.{$uniqueId}@demo-external.org";
        $idempotencyKey = 'IDEM-DUP-' . strtoupper($uniqueId);

        $payload = [
            'event_id' => 'EVT-DUP-1',
            'event_type' => 'employee.created',
            'idempotency_key' => $idempotencyKey,
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'first_name' => 'Marcus',
                'last_name' => 'Aurelius',
                'work_email' => $testEmail,
                'job_title' => 'Managing Director',
                'employment_status' => 'full_time',
                'hire_date' => '2026-02-01',
            ],
        ];

        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp . '.' . json_encode($payload), $this->webhookSecret);

        $headers = [
            'X-SmartHCM-Signature' => 'sha256=' . $signature,
            'X-SmartHCM-Timestamp' => $timestamp,
            'X-Tenant-ID' => $this->tenant->id,
            'Content-Type' => 'application/json',
        ];

        // 1. First delivery - Must succeed
        $firstResponse = $this->withHeaders($headers)->postJson('/api/v1/integrations/webhooks/demo-hr-provider', $payload);
        $firstResponse->assertStatus(202);

        $initialCount = Employee::where('tenant_id', $this->tenant->id)->where('official_email', $testEmail)->count();
        $this->assertSame(1, $initialCount, 'Exactly 1 employee created after first delivery');

        // 2. Second delivery with SAME payload & idempotency key - Must be suppressed!
        $duplicateResponse = $this->withHeaders($headers)->postJson('/api/v1/integrations/webhooks/demo-hr-provider', $payload);
        $duplicateResponse->assertStatus(200);
        $duplicateResponse->assertJsonPath('data.status', 'ignored');
        $duplicateResponse->assertJsonPath('data.reason', 'duplicate_idempotency_key');

        $finalCount = Employee::where('tenant_id', $this->tenant->id)->where('official_email', $testEmail)->count();
        $this->assertSame(1, $finalCount, 'Strict idempotency verified: NO duplicate employee created!');
    }

    public function test_step_7_outbound_integration_push(): void
    {
        $syncEngine = app(SyncEngine::class);

        $syncRun = $syncEngine->triggerEventSync($this->connection->id, [
            'employee_id' => 'EMP-1001',
            'status' => 'promoted',
            'title' => 'Senior Director',
        ], 'outbound');

        $this->assertInstanceOf(IntegrationSyncRun::class, $syncRun);
        $this->assertSame('outbound', $syncRun->sync_type);
    }

    public function test_step_10_deliberate_error_dead_letter_and_ai_diagnosis(): void
    {
        $deadLetterService = app(DeadLetterService::class);
        $aiAssistant = app(IntegrationAiAssistant::class);

        // Record a deliberate authentication failure dead letter
        $deadLetter = $deadLetterService->recordDeadLetter(
            $this->connection->id,
            'inbound_sync',
            ['entity' => 'payroll', 'records' => 50],
            'Authentication failed: 401 Unauthorized - invalid OAuth access token',
            3,
            $this->tenant->id
        );

        $this->assertInstanceOf(IntegrationDeadLetter::class, $deadLetter);
        $this->assertNull($deadLetter->replayed_at);

        // Run AI diagnosis
        $diagnosisResult = $aiAssistant->diagnoseDeadLetter($deadLetter->id, $this->tenant->id);
        $this->assertSame($deadLetter->id, $diagnosisResult['dead_letter_id']);
        $this->assertSame('authentication_failure', $diagnosisResult['diagnosis']['category']);
        $this->assertSame('high', $diagnosisResult['diagnosis']['severity']);
        $this->assertTrue($diagnosisResult['diagnosis']['can_auto_repair']);

        // Test dead letter replay
        $replayed = $deadLetterService->replay($deadLetter->id);
        $this->assertTrue($replayed);

        $deadLetter->refresh();
        $this->assertNotNull($deadLetter->replayed_at, 'Dead letter must be marked as replayed');
    }

    public function test_api_gateway_health_and_openapi_endpoints(): void
    {
        $health = $this->getJson('/api/v1/api-gateway/health');
        $health->assertStatus(200);
        $health->assertJsonPath('data.status', 'operational');

        $openApi = $this->getJson('/api/v1/api-gateway/openapi.json');
        $openApi->assertStatus(200);
        $openApi->assertJsonPath('openapi', '3.0.3');
    }
}
