<?php

declare(strict_types=1);

namespace Flow\Packages\Integrations\Infrastructure\Connectors;

use Flow\Packages\Integrations\Domain\DTOs\ConnectorResult;
use Flow\Packages\Integrations\Domain\DTOs\InboundEvent;
use Illuminate\Support\Str;

final class DemoHrConnector extends AbstractConnector
{
    public function getKey(): string
    {
        return 'demo-hr-provider';
    }

    public function getName(): string
    {
        return 'Demo External HR Provider';
    }

    public function getType(): string
    {
        return 'hr';
    }

    public function getSupportedAuthTypes(): array
    {
        return ['api_key', 'bearer_token'];
    }

    public function authenticate(array $credentials, array $config = []): ConnectorResult
    {
        $apiKey = $credentials['api_key'] ?? $credentials['token'] ?? null;
        if (empty($apiKey)) {
            return ConnectorResult::failure('API key or token is required for Demo HR Provider', 401);
        }

        return ConnectorResult::ok([
            'authenticated' => true,
            'organization' => 'Acme Global Demo',
            'api_version' => 'v2.1',
        ]);
    }

    public function healthCheck(array $credentials, array $config = []): ConnectorResult
    {
        $auth = $this->authenticate($credentials, $config);
        if (!$auth->success) {
            return $auth;
        }

        return ConnectorResult::ok([
            'status' => 'healthy',
            'latency_ms' => 42,
            'provider' => 'Demo HR Provider v2.1',
        ]);
    }

    public function pull(array $context, array $credentials, array $config = []): ConnectorResult
    {
        $auth = $this->authenticate($credentials, $config);
        if (!$auth->success) {
            return $auth;
        }

        $entity = $context['entity'] ?? 'employees';

        if ($entity === 'attendance') {
            return ConnectorResult::ok([
                [
                    'external_id' => 'EXT-ATT-1001',
                    'employee_ext_id' => 'EXT-EMP-001',
                    'timestamp' => now()->subHours(8)->toIso8601String(),
                    'type' => 'check_in',
                    'device_id' => 'BIO-LOBBY-01',
                ],
                [
                    'external_id' => 'EXT-ATT-1002',
                    'employee_ext_id' => 'EXT-EMP-001',
                    'timestamp' => now()->toIso8601String(),
                    'type' => 'check_out',
                    'device_id' => 'BIO-LOBBY-01',
                ],
            ]);
        }

        // Return sample external employees
        return ConnectorResult::ok([
            [
                'external_id' => 'EXT-EMP-001',
                'first_name' => 'Alexander',
                'last_name' => 'Hamilton',
                'email' => 'alexander.hamilton@demo-external.org',
                'department' => 'Treasury',
                'title' => 'Secretary',
                'status' => 'active',
                'hire_date' => '2025-01-15',
                'salary' => 125000,
            ],
            [
                'external_id' => 'EXT-EMP-002',
                'first_name' => 'Eleanor',
                'last_name' => 'Vance',
                'email' => 'eleanor.vance@demo-external.org',
                'department' => 'Research & Development',
                'title' => 'Senior Investigator',
                'status' => 'active',
                'hire_date' => '2025-03-01',
                'salary' => 98000,
            ],
        ]);
    }

    public function push(array $payload, array $credentials, array $config = []): ConnectorResult
    {
        $auth = $this->authenticate($credentials, $config);
        if (!$auth->success) {
            return $auth;
        }

        // Simulate successful push to external system
        return ConnectorResult::ok([
            'external_id' => $payload['external_id'] ?? ('EXT-' . Str::upper(Str::random(6))),
            'status' => 'synced',
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Generate a sample mock webhook payload for testing.
     */
    public function generateSampleWebhook(string $eventType = 'employee.created', array $override = []): array
    {
        $eventId = 'EVT-' . Str::upper(Str::random(8));
        $idempotencyKey = 'IDEM-' . Str::upper(Str::random(12));

        $payload = array_merge([
            'external_employee_id' => 'EXT-DEMO-' . rand(100, 999),
            'first_name' => 'Marcus',
            'last_name' => 'Aurelius',
            'work_email' => 'marcus.aurelius@demo-external.org',
            'department_code' => 'EXEC',
            'job_title' => 'Managing Director',
            'employment_status' => 'full_time',
            'hire_date' => '2026-02-01',
        ], $override);

        return [
            'event_id' => $eventId,
            'event_type' => $eventType,
            'idempotency_key' => $idempotencyKey,
            'timestamp' => now()->toIso8601String(),
            'data' => $payload,
        ];
    }

    public function getManifest(): array
    {
        return [
            'key' => $this->getKey(),
            'name' => $this->getName(),
            'type' => $this->getType(),
            'version' => $this->getVersion(),
            'supported_auth_types' => $this->getSupportedAuthTypes(),
            'entities' => ['employees', 'attendance', 'departments'],
            'config_schema' => [
                'api_key' => ['type' => 'password', 'required' => true, 'label' => 'API Key'],
                'webhook_secret' => ['type' => 'password', 'required' => false, 'label' => 'HMAC Webhook Secret'],
            ],
        ];
    }
}
