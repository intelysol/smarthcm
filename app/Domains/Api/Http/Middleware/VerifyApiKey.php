<?php

declare(strict_types=1);

namespace App\Domains\Api\Http\Middleware;

use App\Domains\Api\Models\ApiClient;
use App\Domains\Api\Models\ApiKey;
use App\Domains\Api\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainKey = $request->header('X-API-Key');

        if (empty($plainKey)) {
            $bearer = $request->bearerToken();
            if (!empty($bearer)) {
                $plainKey = $bearer;
            }
        }

        if (empty($plainKey)) {
            return ApiResponse::error(
                'UNAUTHORIZED',
                'API key is required. Provide via X-API-Key header or Bearer token.',
                [],
                401
            );
        }

        $hash256 = hash('sha256', $plainKey);
        $hash512 = hash('sha512', $plainKey);

        $apiKey = ApiKey::with('client')
            ->whereIn('key_hash', [$hash256, $hash512])
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$apiKey || !$apiKey->client || $apiKey->client->status !== 'active') {
            return ApiResponse::error(
                'INVALID_API_KEY',
                'API key is invalid, revoked, or client is disabled.',
                [],
                401
            );
        }

        // Check client IP allowlist if defined
        $ipAllowlist = $apiKey->client->ip_allowlist;
        if (!empty($ipAllowlist) && is_array($ipAllowlist)) {
            $clientIp = $request->ip();
            if (!in_array($clientIp, $ipAllowlist, true)) {
                return ApiResponse::error(
                    'IP_FORBIDDEN',
                    'Request IP is not in the client allowlist.',
                    ['ip' => $clientIp],
                    403
                );
            }
        }

        // Bind client and tenant to request attributes
        $request->attributes->set('api_key', $apiKey);
        $request->attributes->set('api_client', $apiKey->client);
        $request->attributes->set('tenant_id', $apiKey->client->tenant_id);

        return $next($request);
    }
}
