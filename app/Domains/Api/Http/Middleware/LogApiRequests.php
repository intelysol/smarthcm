<?php

declare(strict_types=1);

namespace App\Domains\Api\Http\Middleware;

use App\Domains\Api\Models\ApiClient;
use App\Domains\Api\Models\ApiRequestLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $correlationId = $request->header('X-Correlation-ID') ?? (string) Str::uuid();

        // Attach correlation ID to request attributes
        $request->attributes->set('correlation_id', $correlationId);

        /** @var Response $response */
        $response = $next($request);

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        $response->headers->set('X-Correlation-ID', $correlationId);

        // Record request log
        try {
            /** @var ApiClient|null $client */
            $client = $request->attributes->get('api_client');
            $tenantId = $request->attributes->get('tenant_id') ?? ($client?->tenant_id);

            ApiRequestLog::create([
                'tenant_id' => $tenantId,
                'api_client_id' => $client?->id,
                'api_endpoint_id' => null,
                'correlation_id' => $correlationId,
                'response_status' => $response->getStatusCode(),
                'latency_ms' => $durationMs,
                'ip_address' => $request->ip(),
                'requested_at' => now(),
            ]);
        } catch (Throwable $e) {
            // Logging should never crash the API request
            report($e);
        }

        return $response;
    }
}
