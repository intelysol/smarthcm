<?php

use App\Http\Middleware\CaptureCorrelationId;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Prepend correlation ID middleware globally to all requests
        $middleware->prepend(CaptureCorrelationId::class);

        // Append enterprise security headers middleware globally to all responses
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);

        // Configure guest redirection for unauthenticated web requests
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            return '/login';
        });

        $middleware->alias([
            'tenant' => \App\Domains\Platform\Http\Middleware\ResolveTenant::class,
            'commercial.entitlement' => \App\Domains\Platform\Http\Middleware\EnforceCommercialEntitlement::class,
            'workspace' => \App\Http\Middleware\EnsureWorkspaceAccess::class,
            'security.headers' => \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            $requestId = (string) ($request->attributes->get('request_id') ?? 'req_' . Str::uuid()->toString());

            // Handle CSRF / Session Expiration for browser requests
            if ($e instanceof TokenMismatchException && ! $request->expectsJson()) {
                return redirect()->guest('/login')->with('warning', 'Your session has expired. Please sign in again.');
            }

            // Handle unauthenticated web requests
            if ($e instanceof AuthenticationException && ! $request->expectsJson() && ! $request->is('api/*')) {
                return redirect()->guest('/login')->with('warning', 'Please sign in to access this page.');
            }

            // Catch any RouteNotFoundException mentioning login
            if ($e instanceof \Symfony\Component\Routing\Exception\RouteNotFoundException && str_contains($e->getMessage(), 'login')) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'UNAUTHENTICATED',
                            'message' => 'Authentication is required to access this resource.',
                            'details' => [],
                        ],
                        'request_id' => $requestId,
                    ], 401, ['X-Request-ID' => $requestId]);
                }

                return redirect()->guest('/login')->with('warning', 'Please sign in to access this page.');
            }

            // For JSON/API requests, return standardized error envelope
            if ($request->expectsJson() || $request->is('api/*')) {
                $status = 500;
                $code = 'INTERNAL_SERVER_ERROR';
                $message = 'An unexpected error occurred.';
                $details = [];

                if ($e instanceof ValidationException) {
                    $status = 422;
                    $code = 'VALIDATION_ERROR';
                    $message = 'The given data was invalid.';
                    $details = $e->errors();
                } elseif ($e instanceof AuthenticationException) {
                    $status = 401;
                    $code = 'UNAUTHENTICATED';
                    $message = 'Authentication is required to access this resource.';
                } elseif ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                    $status = 403;
                    $code = 'FORBIDDEN';
                    $message = $e->getMessage() ?: 'You do not have permission to access this resource.';
                } elseif ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                    $status = 404;
                    $code = 'RESOURCE_NOT_FOUND';
                    $message = 'The requested resource could not be found.';
                } elseif ($e instanceof ThrottleRequestsException) {
                    $status = 429;
                    $code = 'RATE_LIMIT_EXCEEDED';
                    $message = 'Too many requests. Please retry later.';
                } elseif ($e instanceof TokenMismatchException) {
                    $status = 419;
                    $code = 'SESSION_EXPIRED';
                    $message = 'Your session has expired. Please sign in again.';
                } elseif ($e instanceof HttpExceptionInterface) {
                    $status = $e->getStatusCode();
                    $code = 'HTTP_' . $status;
                    $message = $e->getMessage() ?: 'HTTP request failed.';
                } else {
                    // Production masking: never leak SQL statements or stack traces
                    if (! config('app.debug')) {
                        $message = "An unexpected server error occurred. Reference ID: {$requestId}";
                    } else {
                        $message = $e->getMessage();
                        $details = [
                            'exception' => get_class($e),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ];
                    }
                }

                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => $code,
                        'message' => $message,
                        'details' => $details,
                    ],
                    'request_id' => $requestId,
                ], $status, [
                    'X-Request-ID' => $requestId,
                ]);
            }

            return null;
        });
    })->create();
