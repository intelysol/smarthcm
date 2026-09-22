<?php

use App\Domains\Integration\Http\Controllers\IntegrationController;
use App\Domains\Integration\Http\Controllers\WebhookIngressController;
use Illuminate\Support\Facades\Route;

// Public Inbound Webhook Ingress (Protected by HMAC signature and timestamp verification)
Route::post('v1/integrations/webhooks/{source}', [WebhookIngressController::class, 'handle'])
    ->name('api.v1.integrations.webhooks.ingress');

// Authenticated Tenant Integration Management
Route::prefix('v1/integrations')->name('api.v1.integrations.')->middleware(['web', 'auth', 'tenant'])->group(function (): void {
    Route::get('connectors', [IntegrationController::class, 'connectors']);
    Route::get('connections', [IntegrationController::class, 'connections']);
    Route::post('connections', [IntegrationController::class, 'createConnection']);
    Route::post('events', [IntegrationController::class, 'publish']);
    Route::get('events', [IntegrationController::class, 'events']);
    Route::post('webhooks', [IntegrationController::class, 'webhooks']);
});
