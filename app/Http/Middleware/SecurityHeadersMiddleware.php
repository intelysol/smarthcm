<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and attach enterprise defense-in-depth security headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // 1. Prevent MIME Type Sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 2. Prevent Clickjacking (Framing defense)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 3. Referrer Policy: Send full referrer on same-origin, only origin on cross-origin HTTPS
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 4. Permissions Policy: Restrict sensitive browser APIs by default
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // 5. Content Security Policy (Safe enterprise defaults allowing Vite, Tailwind inline styles, and FontAwesome)
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com",
            "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: https:",
            "connect-src 'self' ws: wss:",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // 6. HTTP Strict Transport Security (HSTS) when operating under HTTPS
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
