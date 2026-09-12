<?php
use App\Domains\Integration\Http\Controllers\IntegrationController;
use Illuminate\Support\Facades\Route;
Route::prefix('v1/integrations')->name('api.v1.integrations.')->middleware(['web', 'auth', 'tenant'])->group(function (): void {
    Route::get('connectors', [IntegrationController::class, 'connectors']); Route::get('connections', [IntegrationController::class, 'connections']); Route::post('connections', [IntegrationController::class, 'createConnection']);
    Route::post('events', [IntegrationController::class, 'publish']); Route::get('events', [IntegrationController::class, 'events']); Route::post('webhooks', [IntegrationController::class, 'webhooks']);
});
