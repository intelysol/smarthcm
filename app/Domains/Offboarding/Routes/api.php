<?php

use App\Domains\Offboarding\Http\Controllers\SeparationAiController;
use App\Domains\Offboarding\Http\Controllers\SeparationAnalyticsController;
use App\Domains\Offboarding\Http\Controllers\SeparationClearanceController;
use App\Domains\Offboarding\Http\Controllers\SeparationController;
use App\Domains\Offboarding\Http\Controllers\SeparationHandoverController;
use App\Domains\Offboarding\Http\Controllers\SeparationSelfServiceController;
use App\Domains\Offboarding\Http\Controllers\SeparationSettlementController;
use Illuminate\Support\Facades\Route;

// 1. Employee Self-Service & Post-Exit Separation APIs
Route::middleware('auth')->prefix('v1/me/separation')->group(function (): void {
    Route::get('/', [SeparationSelfServiceController::class, 'getMySeparation']);
    Route::post('resign', [SeparationSelfServiceController::class, 'submitResignation']);
    Route::post('{id}/exit-interview', [SeparationSelfServiceController::class, 'submitExitInterview']);
});

// 2. HR & Manager Enterprise Offboarding APIs
Route::middleware('auth')->prefix('v1/hcm/separations')->group(function (): void {
    Route::get('/', [SeparationController::class, 'index']);
    Route::post('/', [SeparationController::class, 'store']);
    Route::get('{id}', [SeparationController::class, 'show']);
    Route::post('{id}/submit', [SeparationController::class, 'submit']);
    Route::post('{id}/approve', [SeparationController::class, 'approve']);
    Route::post('{id}/reject', [SeparationController::class, 'reject']);
    Route::post('{id}/withdraw', [SeparationController::class, 'withdraw']);
    Route::post('{id}/cancel', [SeparationController::class, 'cancel']);
    Route::post('{id}/execute', [SeparationController::class, 'execute']);
    Route::post('{id}/reverse', [SeparationController::class, 'reverse']);
    Route::post('{id}/override-notice', [SeparationController::class, 'overrideNotice']);

    // Multi-department Clearance
    Route::get('{id}/clearances', [SeparationClearanceController::class, 'index']);
    Route::post('clearance-items/{itemId}/clear', [SeparationClearanceController::class, 'clearItem']);
    Route::post('clearances/{clearanceId}/waive', [SeparationClearanceController::class, 'waiveDepartment']);

    // Handover Management
    Route::get('{id}/handover', [SeparationHandoverController::class, 'show']);
    Route::post('{id}/handover', [SeparationHandoverController::class, 'store']);
    Route::post('handover/{recordId}/verify', [SeparationHandoverController::class, 'verify']);

    // Final Settlement Orchestration
    Route::get('{id}/settlement', [SeparationSettlementController::class, 'show']);
    Route::post('{id}/settlement/snapshot', [SeparationSettlementController::class, 'recordSnapshot']);

    // Analytics
    Route::get('analytics/metrics', [SeparationAnalyticsController::class, 'metrics']);

    // AI Features
    Route::get('{id}/ai/summary', [SeparationAiController::class, 'summarize']);
    Route::get('{id}/ai/draft-relieving-letter', [SeparationAiController::class, 'draftRelievingLetter']);
    Route::post('ai/query', [SeparationAiController::class, 'query']);
});
