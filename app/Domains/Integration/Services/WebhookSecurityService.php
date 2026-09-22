<?php

declare(strict_types=1);

namespace App\Domains\Integration\Services;

use App\Domains\Api\Models\ApiIdempotencyKey;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WebhookSecurityService
{
    /**
     * Verify incoming webhook signature (supports HMAC-SHA256, GitHub, Stripe, Standard).
     */
    public function verifySignature(string $rawPayload, array $headers, string $secret): bool
    {
        if (empty($secret)) {
            return false;
        }

        // Normalize header keys to lowercase
        $normalizedHeaders = [];
        foreach ($headers as $k => $v) {
            $normalizedHeaders[strtolower((string) $k)] = is_array($v) ? ($v[0] ?? '') : (string) $v;
        }

        $signatureHeader = $normalizedHeaders['x-smarthcm-signature']
            ?? $normalizedHeaders['x-hub-signature-256']
            ?? $normalizedHeaders['x-signature-sha256']
            ?? $normalizedHeaders['x-signature']
            ?? null;

        if (empty($signatureHeader)) {
            return false;
        }

        if (str_starts_with($signatureHeader, 'sha256=')) {
            $signatureHeader = substr($signatureHeader, 7);
        }

        // Check if timestamp is included in header or separate
        $timestamp = $normalizedHeaders['x-smarthcm-timestamp'] ?? null;
        if ($timestamp !== null) {
            $expectedWithTs = hash_hmac('sha256', $timestamp . '.' . $rawPayload, $secret);
            if (hash_equals($expectedWithTs, $signatureHeader)) {
                return true;
            }
        }

        $expectedDirect = hash_hmac('sha256', $rawPayload, $secret);
        return hash_equals($expectedDirect, $signatureHeader);
    }

    /**
     * Verify timestamp freshness to prevent replay attacks (default max skew: 300s / 5 min).
     */
    public function verifyTimestamp(array $headers, int $maxSkewSeconds = 300): bool
    {
        $normalizedHeaders = [];
        foreach ($headers as $k => $v) {
            $normalizedHeaders[strtolower((string) $k)] = is_array($v) ? ($v[0] ?? '') : (string) $v;
        }

        $timestamp = $normalizedHeaders['x-smarthcm-timestamp'] ?? $normalizedHeaders['x-timestamp'] ?? null;
        if ($timestamp === null) {
            return true; // Optional if provider doesn't send timestamp header
        }

        $ts = is_numeric($timestamp) ? (int) $timestamp : strtotime($timestamp);
        if ($ts === false || $ts <= 0) {
            return false;
        }

        $currentTime = time();
        return abs($currentTime - $ts) <= $maxSkewSeconds;
    }

    /**
     * Check whether an idempotency key has already been processed for this tenant.
     */
    public function isReplay(string $tenantId, string $idempotencyKey): bool
    {
        if (empty($idempotencyKey)) {
            return false;
        }

        // Check in api_idempotency_keys table
        return ApiIdempotencyKey::where('tenant_id', $tenantId)
            ->where('key', $idempotencyKey)
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Record processed idempotency key to block subsequent duplicates.
     */
    public function recordProcessedKey(
        string $tenantId,
        string $idempotencyKey,
        string $source,
        array $metadata = [],
        int $ttlHours = 24
    ): void {
        if (empty($idempotencyKey)) {
            return;
        }

        ApiIdempotencyKey::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'key' => $idempotencyKey,
            ],
            [
                'method' => 'POST',
                'path' => '/api/v1/integrations/webhooks/' . $source,
                'response_status' => 200,
                'response_body' => ['status' => 'processed', 'source' => $source, 'at' => now()->toIso8601String()],
                'expires_at' => now()->addHours($ttlHours),
            ]
        );
    }

    /**
     * Sign an outgoing webhook payload with timestamp and HMAC-SHA256 signature.
     */
    public function signOutgoingPayload(array $payload, string $secret): array
    {
        $timestamp = (string) time();
        $rawPayload = json_encode($payload);
        $signature = hash_hmac('sha256', $timestamp . '.' . $rawPayload, $secret);

        return [
            'Content-Type' => 'application/json',
            'X-SmartHCM-Signature' => 'sha256=' . $signature,
            'X-SmartHCM-Timestamp' => $timestamp,
        ];
    }
}
