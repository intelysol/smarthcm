<?php

declare(strict_types=1);

namespace Flow\Packages\Integrations\Infrastructure\Connectors;

use Flow\Packages\Integrations\Domain\DTOs\ConnectorResult;
use Throwable;

final class GenericRestConnector extends AbstractConnector
{
    public function getKey(): string
    {
        return 'generic-rest';
    }

    public function getName(): string
    {
        return 'Generic REST Connector';
    }

    public function getType(): string
    {
        return 'generic';
    }

    public function getSupportedAuthTypes(): array
    {
        return ['api_key', 'bearer_token', 'basic_auth', 'oauth2_client_credentials'];
    }

    public function authenticate(array $credentials, array $config = []): ConnectorResult
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        if (empty($baseUrl)) {
            return ConnectorResult::failure('base_url is required for GenericRestConnector authentication', 400);
        }

        // If oauth2 client credentials flow:
        if (($config['auth_type'] ?? '') === 'oauth2_client_credentials') {
            $tokenEndpoint = (string) ($config['token_endpoint'] ?? $baseUrl . '/oauth/token');
            try {
                $client = $this->createHttpClient([], ['retries' => 1]);
                $response = $client->asForm()->post($tokenEndpoint, [
                    'grant_type' => 'client_credentials',
                    'client_id' => $credentials['client_id'] ?? '',
                    'client_secret' => $credentials['client_secret'] ?? '',
                    'scope' => $config['scope'] ?? '',
                ]);

                if ($response->successful()) {
                    return ConnectorResult::ok($response->json());
                }

                return ConnectorResult::failure('OAuth2 authentication failed: ' . $response->body(), $response->status());
            } catch (Throwable $e) {
                return ConnectorResult::failure('OAuth2 request exception: ' . $e->getMessage(), 500);
            }
        }

        // For api_key or bearer_token, perform healthCheck as verification
        return $this->healthCheck($credentials, $config);
    }

    public function healthCheck(array $credentials, array $config = []): ConnectorResult
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $healthEndpoint = (string) ($config['health_endpoint'] ?? '/health');
        $url = $baseUrl . '/' . ltrim($healthEndpoint, '/');

        try {
            $client = $this->createHttpClient($credentials, $config);
            $response = $client->get($url);

            return $this->responseToResult($response);
        } catch (Throwable $e) {
            return ConnectorResult::failure('Health check failed: ' . $e->getMessage(), 500);
        }
    }

    public function pull(array $context, array $credentials, array $config = []): ConnectorResult
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $endpoint = (string) ($context['endpoint'] ?? $config['pull_endpoint'] ?? '/records');
        $queryParams = (array) ($context['query'] ?? []);

        if (!empty($context['cursor'])) {
            $cursorParam = $config['cursor_param'] ?? 'cursor';
            $queryParams[$cursorParam] = $context['cursor'];
        }

        $url = $baseUrl . '/' . ltrim($endpoint, '/');

        try {
            $client = $this->createHttpClient($credentials, $config);
            $response = $client->get($url, $queryParams);

            return $this->responseToResult($response);
        } catch (Throwable $e) {
            return ConnectorResult::failure('Pull request failed: ' . $e->getMessage(), 500);
        }
    }

    public function push(array $payload, array $credentials, array $config = []): ConnectorResult
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $endpoint = (string) ($config['push_endpoint'] ?? '/records');
        $method = strtoupper((string) ($config['push_method'] ?? 'POST'));
        $url = $baseUrl . '/' . ltrim($endpoint, '/');

        try {
            $client = $this->createHttpClient($credentials, $config);
            $response = match ($method) {
                'PUT' => $client->put($url, $payload),
                'PATCH' => $client->patch($url, $payload),
                default => $client->post($url, $payload),
            };

            return $this->responseToResult($response);
        } catch (Throwable $e) {
            return ConnectorResult::failure('Push request failed: ' . $e->getMessage(), 500);
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
                'base_url' => ['type' => 'string', 'required' => true, 'label' => 'Base API URL'],
                'auth_type' => ['type' => 'enum', 'options' => $this->getSupportedAuthTypes(), 'required' => true],
                'health_endpoint' => ['type' => 'string', 'default' => '/health'],
                'pull_endpoint' => ['type' => 'string', 'default' => '/records'],
                'push_endpoint' => ['type' => 'string', 'default' => '/records'],
            ],
        ];
    }
}
