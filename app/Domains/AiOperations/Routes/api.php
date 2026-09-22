<?php

use App\Domains\AiOperations\Http\Controllers\AiOperationsApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('hcm/ai')->group(function () {
    Route::get('/operations/dashboard', [AiOperationsApiController::class, 'dashboard']);
    Route::get('/operations/telemetry', [AiOperationsApiController::class, 'telemetry']);
    Route::post('/operations/telemetry', [AiOperationsApiController::class, 'recordTelemetry']);
    Route::post('/feedback', [AiOperationsApiController::class, 'recordFeedback']);
    Route::get('/evaluations/datasets', [AiOperationsApiController::class, 'datasets']);
    Route::post('/evaluations/runs', [AiOperationsApiController::class, 'runEvaluation']);
    Route::get('/operations/regressions', [AiOperationsApiController::class, 'regressions']);
    Route::get('/operations/improvements', [AiOperationsApiController::class, 'improvements']);
    Route::post('/operations/improvements', [AiOperationsApiController::class, 'createImprovement']);
    Route::get('/operations/readiness', [AiOperationsApiController::class, 'readiness']);
    Route::get('/operations/budgets', [AiOperationsApiController::class, 'budgets']);
    Route::get('/operations/model-benchmarks', [AiOperationsApiController::class, 'modelBenchmarks']);
});
