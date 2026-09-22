<?php

declare(strict_types=1);

namespace Flow\Packages\Integrations\Infrastructure\Connectors;

use Flow\Packages\Integrations\Domain\DTOs\ConnectorResult;
use Throwable;

final class GenericWebhookConnector extends AbstractConnector
{
    public function getKey(): string
    {
        return 'generic-webhook';
    }

    public function getName(): string
    {
        return 'Generic Webhook Connector';
    }

    public function getType(): string
    {
        return 'webhook';
    }

    public function getSupportedAuthTypes(): array
    {
        return ['hmac_signature', 'api_key', 'bearer_token', 'none'];
    }

    public function authenticate(array $credentials, array $config = []): ConnectorResult
    {
        // Webhook connectors authenticate via incoming signatures/tokens
        return ConnectorResult::ok(['status' => 'ready']);
    }

    public function healthCheck(array $credentials, array $config = []): ConnectorResult
    {
        return ConnectorResult::ok(['status' => 'healthy', 'mode' => 'webhook_listener']);
    }

    public function pull(array $context, array $credentials, array $config = []): ConnectorResult
    {
        return ConnectorResult::failure('Pull is not supported for push-only webhook connector', 400);
    }

    public function push(array $payload, array $credentials, array $config = []): ConnectorResult
    {
        $targetUrl = (string) ($config['target_url'] ?? $config['webhook_url'] ?? '');
        if (empty($targetUrl)) {
            return ConnectorResult::failure('target_url is required for outbound webhook dispatch', 400);
        }

        try {
            $client = $this->createHttpClient($credentials, $config);

            // If secret is configured, generate HMAC-SHA256 signature header
            $secret = (string) ($credentials['secret'] ?? $credentials['signing_secret'] ?? '');
            $rawPayload = json_encode($payload);

            if (!empty($secret)) {
                $timestamp = (string) time();
                $signature = hash_hmac('sha256', $timestamp . '.' . $rawPayload, $secret);
                $client = $client->withHeaders([
                    'X-SmartHCM-Signature' => $signature,
                    'X-SmartHCM-Timestamp' => $timestamp,
                ]);
            }

            $response = $client->post($targetUrl, $payload);
            return $this->responseToResult($response);
        } catch (Throwable $e) {
            return ConnectorResult::failure('Webhook dispatch failed: ' . $e->getMessage(), 500);
        }
    }

    public function getManifest(): array
    {
        return [
            'key' => $this->getKey(),
            'name' => $this->getName(),
            'type' => $this->getType(),
            'version' => $this->getVersion(),
            'supported_auth_types' => $this->getSupportedAuthTypes(),
            'config_schema' => [
                'target_url' => ['type' => 'string', 'required' => false, 'label' => 'Outbound Webhook URL'],
                'secret' => ['type' => 'password', 'required' => false, 'label' => 'Signing Secret'],
            ],
        ];
    }
}
