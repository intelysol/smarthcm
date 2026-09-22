<?php

declare(strict_types=1);

namespace Flow\Packages\Integrations\Infrastructure\Connectors;

use Flow\Packages\Integrations\Domain\Contracts\ConnectorInterface;
use Flow\Packages\Integrations\Domain\DTOs\ConnectorResult;
use Flow\Packages\Integrations\Domain\DTOs\InboundEvent;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

abstract class AbstractConnector implements ConnectorInterface
{
    protected int $defaultTimeout = 30;
    protected int $defaultRetries = 3;
    protected int $retryDelayMs = 500;

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getCapabilities(): array
    {
        return ['inbound_sync', 'outbound_sync', 'webhooks', 'pull', 'push'];
    }

    /**
     * Build standard configured Laravel Http PendingRequest.
     */
    protected function createHttpClient(array $credentials, array $config = []): PendingRequest
    {
        $timeout = (int) ($config['timeout'] ?? $this->defaultTimeout);
        $retries = (int) ($config['retries'] ?? $this->defaultRetries);

        $client = Http::timeout($timeout)
            ->withHeaders([
                'User-Agent' => 'SmartHCM-IntegrationHub/' . $this->getVersion(),
                'Accept' => 'application/json',
            ]);

        if ($retries > 0) {
            $client->retry($retries, $this->retryDelayMs, function (Throwable $exception, PendingRequest $request) {
                // Retry on network errors or 5xx server errors, not on 4xx client errors
                if ($exception instanceof \Illuminate\Http\Client\RequestException) {
                    $status = $exception->response->status();
                    return $status >= 500 || $status === 429;
                }
                return true;
            }, throw: false);
        }

        return $this->applyAuthentication($client, $credentials, $config);
    }

    /**
     * Apply credentials to pending request.
     */
    protected function applyAuthentication(PendingRequest $client, array $credentials, array $config): PendingRequest
    {
        $authType = $config['auth_type'] ?? ($this->getSupportedAuthTypes()[0] ?? 'none');

        return match ($authType) {
            'api_key' => $this->applyApiKey($client, $credentials, $config),
            'bearer_token' => $client->withToken($credentials['token'] ?? $credentials['api_key'] ?? ''),
            'basic_auth' => $client->withBasicAuth($credentials['username'] ?? '', $credentials['password'] ?? ''),
            'oauth2_client_credentials', 'oauth2_auth_code' => $client->withToken($credentials['access_token'] ?? ''),
            default => $client,
        };
    }

    protected function applyApiKey(PendingRequest $client, array $credentials, array $config): PendingRequest
    {
        $headerName = $config['api_key_header'] ?? 'X-API-Key';
        $key = $credentials['api_key'] ?? $credentials['key'] ?? '';
        return $client->withHeaders([$headerName => $key]);
    }

    /**
     * Transform response into a ConnectorResult.
     */
    protected function responseToResult(Response $response): ConnectorResult
    {
        $rateLimits = [
            'limit' => $response->header('X-RateLimit-Limit') ?? $response->header('RateLimit-Limit'),
            'remaining' => $response->header('X-RateLimit-Remaining') ?? $response->header('RateLimit-Remaining'),
            'reset' => $response->header('X-RateLimit-Reset') ?? $response->header('RateLimit-Reset'),
        ];

        if ($response->successful()) {
            return ConnectorResult::ok(
                data: $response->json() ?? $response->body(),
                statusCode: $response->status(),
                metadata: [
                    'headers' => array_filter($rateLimits),
                ]
            );
        }

        return ConnectorResult::failure(
            errorMessage: 'HTTP request failed with status ' . $response->status() . ': ' . Str::limit($response->body(), 300),
            statusCode: $response->status(),
            data: $response->json(),
            metadata: array_filter($rateLimits)
        );
    }

    /**
     * Verify HMAC-SHA256 signature for incoming webhooks.
     */
    public function verifyWebhookSignature(string $rawPayload, array $headers, string $secret): bool
    {
        $signatureHeader = $headers['x-signature']
            ?? $headers['x-hub-signature-256']
            ?? $headers['signature']
            ?? $headers['x-signature-sha256']
            ?? null;

        if (empty($signatureHeader) || empty($secret)) {
            return false;
        }

        // Clean prefix like 'sha256='
        if (str_starts_with($signatureHeader, 'sha256=')) {
            $signatureHeader = substr($signatureHeader, 7);
        }

        $expectedSignature = hash_hmac('sha256', $rawPayload, $secret);

        return hash_equals($expectedSignature, $signatureHeader);
    }

    /**
     * Default webhook parsing implementation.
     */
    public function parseWebhookPayload(string $rawPayload, array $headers): ?InboundEvent
    {
        $data = json_decode($rawPayload, true);
        if (!is_array($data)) {
            return null;
        }

        $eventId = (string) ($data['event_id'] ?? $data['id'] ?? $headers['x-event-id'] ?? Str::uuid()->toString());
        $eventType = (string) ($data['event_type'] ?? $data['type'] ?? $headers['x-event-type'] ?? 'generic.event');
        $idempotencyKey = (string) ($data['idempotency_key'] ?? $headers['idempotency-key'] ?? $headers['x-idempotency-key'] ?? $eventId);

        return new InboundEvent(
            eventId: $eventId,
            eventType: $eventType,
            source: $this->getKey(),
            payload: $data['data'] ?? $data,
            idempotencyKey: $idempotencyKey,
            headers: $headers,
            timestamp: $data['timestamp'] ?? now()->toIso8601String()
        );
    }
}
