<?php

use App\Domains\WorkforcePlanning\Http\Controllers\ActualVsPlanController;
use App\Domains\WorkforcePlanning\Http\Controllers\HiringPlanController;
use App\Domains\WorkforcePlanning\Http\Controllers\PositionPlanController;
use App\Domains\WorkforcePlanning\Http\Controllers\WorkforceAiPlanningController;
use App\Domains\WorkforcePlanning\Http\Controllers\WorkforceCostController;
use App\Domains\WorkforcePlanning\Http\Controllers\WorkforcePlanController;
use App\Domains\WorkforcePlanning\Http\Controllers\WorkforcePlanningDashboardController;
use App\Domains\WorkforcePlanning\Http\Controllers\WorkforceScenarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/hcm/workforce-planning')->middleware(['api'])->group(function () {
    // Executive & Dashboard
    Route::get('/dashboard/summary', [WorkforcePlanningDashboardController::class, 'executiveSummary']);

    // Plans Lifecycle
    Route::get('/plans', [WorkforcePlanController::class, 'index']);
    Route::post('/plans', [WorkforcePlanController::class, 'store']);
    Route::get('/plans/{id}', [WorkforcePlanController::class, 'show']);
    Route::post('/plans/{id}/submit', [WorkforcePlanController::class, 'submit']);
    Route::post('/plans/{id}/approve', [WorkforcePlanController::class, 'approve']);
    Route::post('/plans/{id}/lock', [WorkforcePlanController::class, 'lock']);
    Route::post('/plans/{id}/versions', [WorkforcePlanController::class, 'createVersion']);

    // Position Planning
    Route::get('/plans/{planId}/positions', [PositionPlanController::class, 'index']);
    Route::post('/plans/{planId}/positions', [PositionPlanController::class, 'store']);
    Route::post('/positions/{id}/freeze', [PositionPlanController::class, 'freeze']);
    Route::post('/positions/{id}/unfreeze', [PositionPlanController::class, 'unfreeze']);
    Route::post('/positions/{id}/eliminate', [PositionPlanController::class, 'eliminate']);

    // Hiring Plans & Pipeline
    Route::get('/plans/{planId}/hiring-plan', [HiringPlanController::class, 'index']);
    Route::post('/plans/{planId}/hiring-plan', [HiringPlanController::class, 'store']);
    Route::post('/hiring-plans/{id}/handoff', [HiringPlanController::class, 'handoff']);

    // Labor Cost Plans
    Route::get('/plans/{planId}/costs', [WorkforceCostController::class, 'index']);
    Route::post('/plans/{planId}/costs', [WorkforceCostController::class, 'store']);

    // Scenarios & Modeling
    Route::get('/plans/{planId}/scenarios', [WorkforceScenarioController::class, 'index']);
    Route::post('/plans/{planId}/scenarios', [WorkforceScenarioController::class, 'store']);
    Route::post('/scenarios/{id}/simulate', [WorkforceScenarioController::class, 'simulate']);
    Route::post('/scenarios/compare', [WorkforceScenarioController::class, 'compare']);

    // Actual vs Plan
    Route::get('/plans/{planId}/actual-vs-plan', [ActualVsPlanController::class, 'matrix']);

    // AI Planning Assistant
    Route::get('/plans/{planId}/ai/summary', [WorkforceAiPlanningController::class, 'planSummary']);
    Route::get('/scenarios/{scenarioId}/ai/explanation', [WorkforceAiPlanningController::class, 'scenarioVariance']);
    Route::post('/ai/query', [WorkforceAiPlanningController::class, 'query']);
});
