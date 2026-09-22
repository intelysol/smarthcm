<?php

declare(strict_types=1);

namespace App\Domains\Integration\Services;

use App\Domains\Api\Models\ApiRequestLog;
use App\Domains\Integration\Models\IntegrationConnection;
use App\Domains\Integration\Models\IntegrationDeadLetter;
use App\Domains\Integration\Models\IntegrationEvent;
use App\Domains\Integration\Models\IntegrationSyncRun;
use App\Domains\Integration\Models\WebhookDelivery;
use Flow\Packages\Integrations\Infrastructure\Services\ConnectorRegistry;
use Illuminate\Support\Facades\DB;
use Throwable;

class IntegrationMonitoringService
{
    public function __construct(
        protected ConnectorRegistry $registry,
        protected CredentialManager $credentialManager
    ) {}

    /**
     * Get aggregate health and metrics dashboard data for a tenant.
     */
    public function getDashboardMetrics(?string $tenantId = null): array
    {
        $connectionQuery = IntegrationConnection::query();
        $syncRunQuery = IntegrationSyncRun::query();
        $deadLetterQuery = IntegrationDeadLetter::query();
        $apiLogQuery = ApiRequestLog::query();
        $webhookDeliveryQuery = WebhookDelivery::query();

        if ($tenantId) {
            $connectionQuery->where('tenant_id', $tenantId);
            $deadLetterQuery->whereHas('connection', fn($q) => $q->where('tenant_id', $tenantId));
            $apiLogQuery->where('tenant_id', $tenantId);
        }

        $totalConnections = $connectionQuery->count();
        $activeConnections = (clone $connectionQuery)->where('status', 'active')->count();

        $totalSyncRuns = $syncRunQuery->count();
        $completedSyncRuns = (clone $syncRunQuery)->where('status', 'completed')->count();
        $failedSyncRuns = (clone $syncRunQuery)->where('status', 'failed')->count();

        $deadLetterCount = $deadLetterQuery->whereNull('replayed_at')->count();

        $apiLogs24h = (clone $apiLogQuery)->where('requested_at', '>=', now()->subHours(24));
        $totalApiCalls = $apiLogs24h->count();
        $avgApiLatency = round((float) $apiLogs24h->avg('latency_ms'), 1);
        $errorApiCalls = (clone $apiLogs24h)->where('response_status', '>=', 400)->count();

        $totalWebhookDeliveries = $webhookDeliveryQuery->count();
        $failedWebhookDeliveries = (clone $webhookDeliveryQuery)->where('status', 'like', '%failed%')->count();

        return [
            'connections' => [
                'total' => $totalConnections,
                'active' => $activeConnections,
                'inactive' => max(0, $totalConnections - $activeConnections),
            ],
            'sync_runs' => [
                'total' => $totalSyncRuns,
                'completed' => $completedSyncRuns,
                'failed' => $failedSyncRuns,
                'success_rate_percent' => $totalSyncRuns > 0 ? round(($completedSyncRuns / $totalSyncRuns) * 100, 1) : 100.0,
            ],
            'dead_letters' => [
                'unresolved' => $deadLetterCount,
            ],
            'api_gateway' => [
                'requests_24h' => $totalApiCalls,
                'error_count' => $errorApiCalls,
                'error_rate_percent' => $totalApiCalls > 0 ? round(($errorApiCalls / $totalApiCalls) * 100, 2) : 0.0,
                'avg_latency_ms' => $avgApiLatency,
            ],
            'webhooks' => [
                'total_deliveries' => $totalWebhookDeliveries,
                'failed_deliveries' => $failedWebhookDeliveries,
            ],
        ];
    }

    /**
     * Run live health check across all active connections.
     */
    public function checkConnectionHealth(string $connectionId): array
    {
        $connection = IntegrationConnection::with('connector')->findOrFail($connectionId);
        $connectorKey = $connection->connector?->key;

        if (!$connectorKey || !$this->registry->has($connectorKey)) {
            return [
                'status' => 'error',
                'message' => "Connector [{$connectorKey}] is not registered in system.",
            ];
        }

        try {
            $connector = $this->registry->get($connectorKey);
            $credentials = $this->credentialManager->resolveCredentials($connection->id);
            $result = $connector->healthCheck($credentials, $connection->configuration ?? []);

            return [
                'status' => $result->success ? 'healthy' : 'degraded',
                'status_code' => $result->statusCode,
                'details' => $result->data ?? $result->errorMessage,
                'checked_at' => now()->toIso8601String(),
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'unhealthy',
                'message' => $e->getMessage(),
                'checked_at' => now()->toIso8601String(),
            ];
        }
    }
}
