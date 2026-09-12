<?php

use App\Domains\Rules\Http\Controllers\BusinessRuleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/rules')->name('api.v1.rules.')->middleware(['web', 'auth', 'tenant'])->group(function (): void {
    Route::get('/', [BusinessRuleController::class, 'index']);
    Route::post('/', [BusinessRuleController::class, 'store']);
    Route::get('{rule}', [BusinessRuleController::class, 'show']);
    Route::post('{rule}/transition', [BusinessRuleController::class, 'transition']);
    Route::post('{rule}/test', [BusinessRuleController::class, 'test']);
    Route::post('{rule}/execute', [BusinessRuleController::class, 'execute']);
    Route::get('{rule}/versions', [BusinessRuleController::class, 'versions']);
    Route::post('{rule}/versions', [BusinessRuleController::class, 'cloneVersion']);
    Route::post('{rule}/rollback', [BusinessRuleController::class, 'rollback']);
    Route::get('{rule}/executions', [BusinessRuleController::class, 'executions']);
});
