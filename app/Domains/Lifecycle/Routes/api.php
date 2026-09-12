<?php

use App\Domains\Lifecycle\Http\Controllers\PersonnelActionAiController;
use App\Domains\Lifecycle\Http\Controllers\PersonnelActionAnalyticsController;
use App\Domains\Lifecycle\Http\Controllers\PersonnelActionAssignmentController;
use App\Domains\Lifecycle\Http\Controllers\PersonnelActionBulkController;
use App\Domains\Lifecycle\Http\Controllers\PersonnelActionController;
use App\Domains\Lifecycle\Http\Controllers\PersonnelActionImpactController;
use App\Domains\Lifecycle\Http\Controllers\PersonnelActionReversalController;
use App\Domains\Lifecycle\Http\Controllers\PersonnelActionSelfServiceController;
use Illuminate\Support\Facades\Route;

// 1. Employee Self-Service Lifecycle APIs
Route::middleware('auth')->prefix('v1/me/personnel-actions')->group(function (): void {
    Route::get('/', [PersonnelActionSelfServiceController::class, 'getMyActions']);
    Route::post('{id}/acknowledge', [PersonnelActionSelfServiceController::class, 'acknowledge']);
});

// 2. HR & Manager Enterprise Personnel Action APIs
Route::middleware('auth')->prefix('v1/hcm/personnel-actions')->group(function (): void {
    // Actions CRUD & Lifecycle transitions
    Route::get('/', [PersonnelActionController::class, 'index']);
    Route::post('/', [PersonnelActionController::class, 'store']);
    Route::get('{id}', [PersonnelActionController::class, 'show']);
    Route::post('{id}/submit', [PersonnelActionController::class, 'submit']);
    Route::post('{id}/approve', [PersonnelActionController::class, 'approve']);
    Route::post('{id}/reject', [PersonnelActionController::class, 'reject']);
    Route::post('{id}/cancel', [PersonnelActionController::class, 'cancel']);

    // Impact Analysis
    Route::get('{id}/impact', [PersonnelActionImpactController::class, 'analyze']);

    // Reversals & Compensating Actions
    Route::post('{id}/reverse', [PersonnelActionReversalController::class, 'reverse']);

    // Temporary & Acting Assignments
    Route::get('assignments/list', [PersonnelActionAssignmentController::class, 'index']);
    Route::post('assignments', [PersonnelActionAssignmentController::class, 'store']);
    Route::post('assignments/{id}/revert', [PersonnelActionAssignmentController::class, 'revert']);

    // Bulk Operations
    Route::post('bulk/batch', [PersonnelActionBulkController::class, 'createBatch']);
    Route::post('bulk/batches/{id}/dry-run', [PersonnelActionBulkController::class, 'dryRun']);
    Route::post('bulk/batches/{id}/execute', [PersonnelActionBulkController::class, 'execute']);

    // Analytics
    Route::get('analytics/metrics', [PersonnelActionAnalyticsController::class, 'metrics']);

    // AI Features
    Route::get('{id}/ai/summary', [PersonnelActionAiController::class, 'summarize']);
    Route::get('{id}/ai/draft-letter', [PersonnelActionAiController::class, 'draftPromotionLetter']);
    Route::post('ai/query', [PersonnelActionAiController::class, 'query']);
});
