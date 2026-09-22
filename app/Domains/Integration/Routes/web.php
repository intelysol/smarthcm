<?php

use App\Domains\Integration\Http\Controllers\IntegrationWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->prefix('hub/integrations')->name('hub.integrations.')->group(function (): void {
    Route::get('/', [IntegrationWebController::class, 'index'])->name('index');
    Route::post('connections/{connection}/sync', [IntegrationWebController::class, 'triggerSync'])->name('sync');
    Route::get('connections/{connection}/test', [IntegrationWebController::class, 'testConnection'])->name('test');
    Route::post('dead-letters/{id}/replay', [IntegrationWebController::class, 'replayDeadLetter'])->name('dead_letter.replay');
    Route::get('dead-letters/{id}/diagnose', [IntegrationWebController::class, 'diagnoseDeadLetter'])->name('dead_letter.diagnose');
});
