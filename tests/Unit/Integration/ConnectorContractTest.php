<?php

declare(strict_types=1);

namespace Tests\Unit\Integration;

use App\Domains\Integration\Services\CredentialManager;
use App\Domains\Integration\Services\IntegrationAiAssistant;
use App\Domains\Integration\Services\WebhookSecurityService;
use Flow\Packages\Integrations\Domain\Contracts\ConnectorInterface;
use Flow\Packages\Integrations\Domain\DTOs\ConnectorResult;
use Flow\Packages\Integrations\Infrastructure\Connectors\DemoHrConnector;
use Flow\Packages\Integrations\Infrastructure\Connectors\GenericRestConnector;
use Flow\Packages\Integrations\Infrastructure\Connectors\GenericWebhookConnector;
use Flow\Packages\Integrations\Infrastructure\Services\ConnectorRegistry;
use Tests\TestCase;

class ConnectorContractTest extends TestCase
{
    public function test_connectors_implement_connector_interface(): void
    {
        $registry = new ConnectorRegistry();

        $this->assertTrue($registry->has('generic-rest'));
        $this->assertTrue($registry->has('generic-webhook'));
        $this->assertTrue($registry->has('demo-hr-provider'));

        $rest = $registry->get('generic-rest');
        $this->assertInstanceOf(ConnectorInterface::class, $rest);
        $this->assertSame('generic-rest', $rest->getKey());
        $this->assertNotEmpty($rest->getSupportedAuthTypes());

        $webhook = $registry->get('generic-webhook');
        $this->assertInstanceOf(ConnectorInterface::class, $webhook);
        $this->assertSame('generic-webhook', $webhook->getKey());

        $demo = $registry->get('demo-hr-provider');
        $this->assertInstanceOf(ConnectorInterface::class, $demo);
        $this->assertSame('demo-hr-provider', $demo->getKey());
    }

    public function test_credential_manager_masks_sensitive_values(): void
    {
        $manager = app(CredentialManager::class);

        $credentials = [
            'api_key' => 'secret_api_token_12345678',
            'client_secret' => 'super_sensitive_client_secret_9999',
            'base_url' => 'https://api.example.com/v1',
            'nested' => [
                'token' => 'nested_bearer_token_abcd',
            ],
        ];

        $masked = $manager->maskCredentials($credentials);

        $this->assertStringStartsWith('secr', $masked['api_key']);
        $this->assertStringEndsWith('5678', $masked['api_key']);
        $this->assertStringContainsString('****', $masked['api_key']);

        $this->assertStringStartsWith('supe', $masked['client_secret']);
        $this->assertStringEndsWith('9999', $masked['client_secret']);

        // Non-sensitive values remain unmasked
        $this->assertSame('https://api.example.com/v1', $masked['base_url']);

        // Nested values are masked
        $this->assertStringStartsWith('nest', $masked['nested']['token']);
        $this->assertStringEndsWith('abcd', $masked['nested']['token']);
    }

    public function test_webhook_security_hmac_sha256_verification(): void
    {
        $security = app(WebhookSecurityService::class);
        $secret = 'whsec_enterprise_hcm_vault_key_42';
        $payload = ['event_id' => 'EVT-900', 'status' => 'active'];

        $headers = $security->signOutgoingPayload($payload, $secret);
        $rawPayload = json_encode($payload);

        $this->assertTrue($security->verifySignature($rawPayload, $headers, $secret));
        $this->assertFalse($security->verifySignature($rawPayload . 'tampered', $headers, $secret));
        $this->assertFalse($security->verifySignature($rawPayload, $headers, 'wrong_secret'));
    }

    public function test_webhook_timestamp_skew_validation(): void
    {
        $security = app(WebhookSecurityService::class);

        // Fresh timestamp
        $validHeaders = ['X-SmartHCM-Timestamp' => (string) time()];
        $this->assertTrue($security->verifyTimestamp($validHeaders, 300));

        // Expired timestamp (10 minutes old)
        $expiredHeaders = ['X-SmartHCM-Timestamp' => (string) (time() - 600)];
        $this->assertFalse($security->verifyTimestamp($expiredHeaders, 300));
    }

    public function test_ai_assistant_suggests_field_mappings(): void
    {
        $ai = app(IntegrationAiAssistant::class);
        $sample = [
            'first_name' => 'Sara',
            'last_name' => 'Connor',
            'work_email' => 'sara@resistance.org',
            'job_title' => 'Commander',
        ];

        $suggestions = $ai->suggestFieldMappings($sample, 'employee');

        $this->assertArrayHasKey('first_name', $suggestions);
        $this->assertArrayHasKey('last_name', $suggestions);
        $this->assertArrayHasKey('work_email', $suggestions);
        $this->assertArrayHasKey('job_title', $suggestions);
        $this->assertSame('first_name', $suggestions['first_name']['source_field']);
    }
}
