<?php

declare(strict_types=1);

namespace App\Domains\Integration\Services;

use App\Domains\Integration\Models\IntegrationConnection;
use Flow\Packages\Integrations\Domain\Contracts\CredentialManagerInterface;
use Flow\Packages\Integrations\Infrastructure\Services\ConnectorRegistry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class CredentialManager implements CredentialManagerInterface
{
    public function __construct(
        protected ?ConnectorRegistry $registry = null
    ) {
        $this->registry = $registry ?? app(ConnectorRegistry::class);
    }

    /**
     * Resolve decrypted credentials for a given connection ID.
     */
    public function resolveCredentials(string $connectionId): array
    {
        $connection = IntegrationConnection::findOrFail($connectionId);
        $credentials = $connection->encrypted_credentials ?? [];

        return is_array($credentials) ? $credentials : [];
    }

    /**
     * Store and encrypt credentials for a given connection ID.
     */
    public function storeCredentials(string $connectionId, array $credentials): bool
    {
        $connection = IntegrationConnection::findOrFail($connectionId);
        $connection->encrypted_credentials = $credentials;
        return $connection->save();
    }

    /**
     * Mask sensitive credential fields.
     */
    public function maskCredentials(array $credentials): array
    {
        $sensitiveKeys = [
            'api_key', 'key', 'token', 'access_token', 'refresh_token',
            'client_secret', 'secret', 'password', 'private_key', 'webhook_secret',
            'auth_token', 'signing_secret',
        ];

        $masked = [];
        foreach ($credentials as $key => $value) {
            if (is_array($value)) {
                $masked[$key] = $this->maskCredentials($value);
            } elseif (in_array(strtolower((string) $key), $sensitiveKeys, true) && is_string($value)) {
                $len = strlen($value);
                if ($len <= 8) {
                    $masked[$key] = '********';
                } else {
                    $prefix = substr($value, 0, 4);
                    $suffix = substr($value, -4);
                    $masked[$key] = "{$prefix}****{$suffix}";
                }
            } else {
                $masked[$key] = $value;
            }
        }

        return $masked;
    }

    /**
     * Refresh OAuth2 access token if connection uses OAuth2.
     */
    public function refreshOAuthToken(string $connectionId): array
    {
        $connection = IntegrationConnection::with('connector')->findOrFail($connectionId);
        $credentials = $connection->encrypted_credentials ?? [];
        $config = $connection->configuration ?? [];

        $refreshToken = $credentials['refresh_token'] ?? null;
        $tokenUrl = $config['token_endpoint'] ?? $config['token_url'] ?? null;
        $clientId = $credentials['client_id'] ?? null;
        $clientSecret = $credentials['client_secret'] ?? null;

        if (empty($tokenUrl) || empty($clientId) || empty($clientSecret)) {
            throw new InvalidArgumentException("Missing OAuth2 refresh configuration for connection [{$connectionId}].");
        }

        try {
            $response = Http::asForm()->post($tokenUrl, [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);

            if (!$response->successful()) {
                throw new RuntimeException("OAuth2 refresh request failed: " . $response->body());
            }

            $data = $response->json();
            $credentials['access_token'] = $data['access_token'] ?? $credentials['access_token'];
            if (!empty($data['refresh_token'])) {
                $credentials['refresh_token'] = $data['refresh_token'];
            }

            $expiresIn = (int) ($data['expires_in'] ?? 3600);
            $connection->credential_expires_at = now()->addSeconds($expiresIn);
            $connection->encrypted_credentials = $credentials;
            $connection->save();

            return $credentials;
        } catch (Throwable $e) {
            throw new RuntimeException("Error refreshing OAuth2 token: " . $e->getMessage(), 0, $e);
        }
    }
}
