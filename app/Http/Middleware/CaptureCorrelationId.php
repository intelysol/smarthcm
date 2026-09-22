<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CaptureCorrelationId
{
    public const HEADER_NAME = 'X-Request-ID';
    public const CORRELATION_HEADER = 'X-Correlation-ID';

    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->header(self::HEADER_NAME)
            ?? $request->header(self::CORRELATION_HEADER)
            ?? 'req_' . Str::uuid()->toString();

        $correlationId = $request->header(self::CORRELATION_HEADER) ?? $requestId;

        // Store in request attributes
        $request->attributes->set('request_id', $requestId);
        $request->attributes->set('correlation_id', $correlationId);

        // Share globally with logging context
        Log::shareContext([
            'request_id' => $requestId,
            'correlation_id' => $correlationId,
            'ip' => $request->ip(),
            'uri' => $request->path(),
            'method' => $request->method(),
        ]);

        if (class_exists(Context::class)) {
            Context::add('request_id', $requestId);
            Context::add('correlation_id', $correlationId);
        }

        $response = $next($request);

        // Attach correlation headers to outgoing response
        $response->headers->set(self::HEADER_NAME, $requestId);
        $response->headers->set(self::CORRELATION_HEADER, $correlationId);

        return $response;
    }
}
