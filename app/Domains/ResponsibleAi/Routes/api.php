<?php

use App\Domains\ResponsibleAi\Http\Controllers\ResponsibleAiGovernanceApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('ai/governance')->middleware('api')->group(function () {
    Route::get('/dashboard', [ResponsibleAiGovernanceApiController::class, 'dashboard']);
    Route::post('/use-cases', [ResponsibleAiGovernanceApiController::class, 'registerUseCase']);
    Route::post('/models', [ResponsibleAiGovernanceApiController::class, 'registerModel']);
    Route::post('/kill-switch', [ResponsibleAiGovernanceApiController::class, 'killSwitch']);
    Route::get('/eligibility', [ResponsibleAiGovernanceApiController::class, 'checkEligibility']);
    Route::post('/incidents', [ResponsibleAiGovernanceApiController::class, 'logIncident']);
    Route::post('/fairness/evaluate', [ResponsibleAiGovernanceApiController::class, 'evaluateFairness']);
});
