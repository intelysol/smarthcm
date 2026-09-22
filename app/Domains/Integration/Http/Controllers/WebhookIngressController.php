<?php

declare(strict_types=1);

namespace App\Domains\Integration\Http\Controllers;

use App\Domains\Api\Support\ApiResponse;
use App\Domains\Integration\Jobs\InboundSyncJob;
use App\Domains\Integration\Models\IntegrationConnection;
use App\Domains\Integration\Models\IntegrationConnector;
use App\Domains\Integration\Models\IntegrationEvent;
use App\Domains\Integration\Services\WebhookSecurityService;
use Flow\Packages\Integrations\Infrastructure\Services\ConnectorRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookIngressController
{
    public function __construct(
        protected ConnectorRegistry $registry,
        protected WebhookSecurityService $securityService
    ) {}

    public function handle(Request $request, string $source): JsonResponse
    {
        $rawPayload = $request->getContent();
        $headers = $request->headers->all();

        // Find matching connector or connection
        $connector = $this->registry->has($source) ? $this->registry->get($source) : null;
        if (!$connector && $this->registry->has('generic-webhook')) {
            $connector = $this->registry->get('generic-webhook');
        }

        if (!$connector) {
            return ApiResponse::error('CONNECTOR_NOT_FOUND', "Unsupported webhook source [{$source}].", [], 404);
        }

        // Parse payload
        $inboundEvent = $connector->parseWebhookPayload($rawPayload, $headers);
        if (!$inboundEvent) {
            return ApiResponse::error('INVALID_PAYLOAD', 'Payload must be valid JSON.', [], 400);
        }

        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');

        // Look up active connection for this source/tenant if possible
        $connection = null;
        if ($tenantId) {
            $connection = IntegrationConnection::where('tenant_id', $tenantId)
                ->whereHas('connector', fn($q) => $q->where('key', $source))
                ->first();
        }

        if (!$connection) {
            $connection = IntegrationConnection::whereHas('connector', fn($q) => $q->where('key', $source))->first();
            if ($connection) {
                $tenantId = $connection->tenant_id;
            }
        }

        if (!$tenantId) {
            $tenantId = 'global';
        }

        // Validate webhook signature if secret configured
        $secret = $connection?->encrypted_credentials['webhook_secret'] ?? config('services.integrations.default_secret', '');
        if (!empty($secret)) {
            $isValid = $connector->verifyWebhookSignature($rawPayload, $headers, $secret)
                || $this->securityService->verifySignature($rawPayload, $headers, $secret);

            if (!$isValid) {
                return ApiResponse::error('INVALID_SIGNATURE', 'HMAC webhook signature verification failed.', [], 401);
            }
        }

        // Idempotency / Replay suppression check
        $idempotencyKey = $inboundEvent->idempotencyKey ?? $inboundEvent->eventId;
        if ($this->securityService->isReplay($tenantId, $idempotencyKey)) {
            return ApiResponse::success([
                'status' => 'ignored',
                'reason' => 'duplicate_idempotency_key',
                'idempotency_key' => $idempotencyKey,
            ], ['replayed' => true]);
        }

        // Record integration event
        $eventRecord = IntegrationEvent::create([
            'tenant_id' => $tenantId,
            'event_type' => $inboundEvent->eventType,
            'payload' => $inboundEvent->payload,
            'subject_type' => 'webhook.' . $source,
            'subject_id' => $inboundEvent->eventId,
            'occurred_at' => now(),
        ]);

        // Record idempotency key
        $this->securityService->recordProcessedKey($tenantId, $idempotencyKey, $source, [
            'event_id' => $eventRecord->id,
            'event_type' => $inboundEvent->eventType,
        ]);

        // Dispatch inbound processing
        if ($connection) {
            InboundSyncJob::dispatch([
                'connection_id' => $connection->id,
                'entity_type' => 'employee',
                'payload' => $inboundEvent->payload,
            ]);
        }

        return ApiResponse::success([
            'status' => 'accepted',
            'event_id' => $eventRecord->id,
            'idempotency_key' => $idempotencyKey,
            'processed' => true,
        ])->setStatusCode(202);
    }
}
