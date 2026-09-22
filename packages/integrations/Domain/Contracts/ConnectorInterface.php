<?php

declare(strict_types=1);

namespace Flow\Packages\Integrations\Domain\Contracts;

use Flow\Packages\Integrations\Domain\DTOs\ConnectorResult;
use Flow\Packages\Integrations\Domain\DTOs\InboundEvent;

interface ConnectorInterface
{
    /**
     * Get unique identifier/key of the connector (e.g. 'generic-rest', 'generic-webhook', 'demo-hr-provider').
     */
    public function getKey(): string;

    /**
     * Human-readable display name.
     */
    public function getName(): string;

    /**
     * Connector category (e.g. 'hr', 'payroll', 'erp', 'biometric', 'generic', 'benefits').
     */
    public function getType(): string;

    /**
     * Semantic version of connector implementation.
     */
    public function getVersion(): string;

    /**
     * Array of supported authentication types.
     */
    public function getSupportedAuthTypes(): array;

    /**
     * Capabilities supported (e.g. ['inbound_sync', 'outbound_sync', 'webhooks', 'pull', 'push']).
     */
    public function getCapabilities(): array;

    /**
     * Authenticate or validate connection credentials.
     */
    public function authenticate(array $credentials, array $config = []): ConnectorResult;

    /**
     * Perform live health check on external endpoint.
     */
    public function healthCheck(array $credentials, array $config = []): ConnectorResult;

    /**
     * Pull external records into normalized payload array.
     */
    public function pull(array $context, array $credentials, array $config = []): ConnectorResult;

    /**
     * Push transformed HCM payload to external system.
     */
    public function push(array $payload, array $credentials, array $config = []): ConnectorResult;

    /**
     * Verify inbound webhook signature.
     */
    public function verifyWebhookSignature(string $rawPayload, array $headers, string $secret): bool;

    /**
     * Parse inbound webhook payload into normalized InboundEvent.
     */
    public function parseWebhookPayload(string $rawPayload, array $headers): ?InboundEvent;

    /**
     * Get connector configuration schema / manifest.
     */
    public function getManifest(): array;
}
