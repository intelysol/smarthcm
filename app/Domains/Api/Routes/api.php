<?php

use App\Domains\Api\Http\Controllers\ApiCatalogController;
use App\Domains\Api\Http\Controllers\ApiGovernanceController;
use App\Domains\Api\Http\Middleware\ApiIdempotencyMiddleware;
use App\Domains\Api\Http\Middleware\EnforceApiRateLimits;
use App\Domains\Api\Http\Middleware\EnforceApiScopes;
use App\Domains\Api\Http\Middleware\LogApiRequests;
use App\Domains\Api\Http\Middleware\VerifyApiKey;
use Illuminate\Support\Facades\Route;

// Public / Discovery Endpoints
Route::prefix('v1/api-gateway')->group(function (): void {
    Route::get('catalog', [ApiCatalogController::class, 'catalog']);
    Route::get('openapi.json', [ApiCatalogController::class, 'openApiSpec']);
    Route::get('health', [ApiCatalogController::class, 'health']);
});

// Platform Governance UI (Web Authenticated)
Route::prefix('v1/platform/api')->middleware(['web', 'auth', 'tenant'])->group(function (): void {
    Route::get('catalog', [ApiGovernanceController::class, 'catalog']);
    Route::get('clients', [ApiGovernanceController::class, 'clients']);
    Route::post('clients', [ApiGovernanceController::class, 'createClient']);
    Route::post('clients/{client}/revoke', [ApiGovernanceController::class, 'revoke']);
});

// Gateway-Protected External API Endpoints (Secured by API Key, Rate Limiting, Idempotency & Audit Logging)
Route::prefix('v1/external')
    ->middleware([
        LogApiRequests::class,
        VerifyApiKey::class,
        EnforceApiRateLimits::class,
        ApiIdempotencyMiddleware::class,
    ])
    ->group(function (): void {
        Route::get('ping', fn (\Illuminate\Http\Request $r) => response()->json([
            'status' => 'pong',
            'authenticated' => true,
            'tenant_id' => $r->attributes->get('tenant_id'),
        ]));
    });
