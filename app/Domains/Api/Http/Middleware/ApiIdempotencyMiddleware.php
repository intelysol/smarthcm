<?php

declare(strict_types=1);

namespace App\Domains\Api\Http\Middleware;

use App\Domains\Api\Models\ApiIdempotencyKey;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiIdempotencyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply idempotency to state-mutating HTTP methods
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        $idempotencyKey = $request->header('Idempotency-Key') ?? $request->header('X-Idempotency-Key');

        if (empty($idempotencyKey)) {
            return $next($request);
        }

        $tenantId = (string) ($request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID') ?? 'global');

        // Check if idempotency key already exists
        $existing = ApiIdempotencyKey::where('tenant_id', $tenantId)
            ->where('key', $idempotencyKey)
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            $responseBody = $existing->response_body ?? [];
            $statusCode = (int) ($existing->response_status ?? 200);

            return response()->json($responseBody, $statusCode, [
                'X-Idempotency-Replayed' => 'true',
                'X-Idempotency-Key' => $idempotencyKey,
            ]);
        }

        /** @var Response $response */
        $response = $next($request);

        // Store response only if status is 2xx or 4xx
        if ($response->getStatusCode() < 500) {
            $content = $response->getContent();
            $decoded = json_decode($content, true);

            ApiIdempotencyKey::create([
                'tenant_id' => $tenantId,
                'key' => $idempotencyKey,
                'method' => $request->method(),
                'path' => $request->path(),
                'response_status' => $response->getStatusCode(),
                'response_body' => $decoded ?? ['raw' => $content],
                'expires_at' => now()->addHours(24),
            ]);

            $response->headers->set('X-Idempotency-Replayed', 'false');
            $response->headers->set('X-Idempotency-Key', $idempotencyKey);
        }

        return $response;
    }
}
