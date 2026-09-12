<?php

use App\Domains\WorkforceIntelligence\Http\Controllers\WorkforceIntelligenceApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('hcm/workforce-intelligence')->middleware('api')->group(function () {
    Route::get('/scorecard', [WorkforceIntelligenceApiController::class, 'executiveScorecard']);
    Route::get('/health-index', [WorkforceIntelligenceApiController::class, 'healthIndex']);
    Route::get('/pulse', [WorkforceIntelligenceApiController::class, 'pulse']);
    Route::get('/risks', [WorkforceIntelligenceApiController::class, 'risks']);
    Route::get('/alerts', [WorkforceIntelligenceApiController::class, 'alerts']);
    Route::get('/decisions', [WorkforceIntelligenceApiController::class, 'decisionQueue']);
    Route::post('/decisions/{decisionId}/action', [WorkforceIntelligenceApiController::class, 'processDecision']);
    Route::post('/explain', [WorkforceIntelligenceApiController::class, 'explainAnomaly']);
    Route::post('/ask-ai', [WorkforceIntelligenceApiController::class, 'askAi']);
});
