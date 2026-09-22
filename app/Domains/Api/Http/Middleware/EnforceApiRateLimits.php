<?php

declare(strict_types=1);

namespace App\Domains\Api\Http\Middleware;

use App\Domains\Api\Models\ApiClient;
use App\Domains\Api\Models\ApiRateLimitPolicy;
use App\Domains\Api\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnforceApiRateLimits
{
    public function handle(Request $request, Closure $next, ?string $policyName = null): Response
    {
        /** @var ApiClient|null $client */
        $client = $request->attributes->get('api_client');
        $identifier = $client ? $client->id : ($request->ip() ?? 'global');

        // Look up custom policy from api_rate_limit_policies
        $policy = null;
        if ($client) {
            $policy = ApiRateLimitPolicy::where('api_client_id', $client->id)->first();
        }

        $limit = $policy ? (int) $policy->requests_per_minute : 60;
        $burst = $policy ? (int) ($policy->burst_limit ?? $limit) : $limit;

        $key = 'rate_limit:api:' . $identifier . ':' . date('YmdHi');
        $current = (int) Cache::get($key, 0);

        $windowReset = (60 - (int) date('s'));

        if ($current >= $limit) {
            $response = ApiResponse::error(
                'RATE_LIMIT_EXCEEDED',
                'Too many API requests. Please respect rate limits.',
                ['limit' => $limit, 'retry_after_seconds' => $windowReset],
                429
            );

            $response->headers->set('X-RateLimit-Limit', (string) $limit);
            $response->headers->set('X-RateLimit-Remaining', '0');
            $response->headers->set('X-RateLimit-Reset', (string) (time() + $windowReset));
            $response->headers->set('Retry-After', (string) $windowReset);

            return $response;
        }

        Cache::put($key, $current + 1, 65);
        $remaining = max(0, $limit - ($current + 1));

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', (string) $limit);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);
        $response->headers->set('X-RateLimit-Reset', (string) (time() + $windowReset));

        return $response;
    }
}
