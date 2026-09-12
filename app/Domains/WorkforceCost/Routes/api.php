<?php

use App\Domains\WorkforceCost\Http\Controllers\AdvisoryWorkforceCostAiController;
use App\Domains\WorkforceCost\Http\Controllers\LaborCostAllocationController;
use App\Domains\WorkforceCost\Http\Controllers\WorkforceCostForecastController;
use App\Domains\WorkforceCost\Http\Controllers\WorkforceCostReconciliationController;
use App\Domains\WorkforceCost\Http\Controllers\WorkforceCostScenarioController;
use App\Domains\WorkforceCost\Http\Controllers\WorkforceCostSnapshotController;
use App\Domains\WorkforceCost\Http\Controllers\WorkforceCostVarianceController;
use App\Domains\WorkforceCost\Http\Controllers\WorkforceEconomicsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/hcm/workforce-cost')->middleware(['api'])->group(function () {
    // Snapshots
    Route::get('/snapshots', [WorkforceCostSnapshotController::class, 'index']);
    Route::post('/snapshots', [WorkforceCostSnapshotController::class, 'store']);
    Route::get('/snapshots/{id}', [WorkforceCostSnapshotController::class, 'show']);

    // Allocation
    Route::get('/allocations/rules', [LaborCostAllocationController::class, 'indexRules']);
    Route::post('/allocations/rules', [LaborCostAllocationController::class, 'storeRule']);
    Route::post('/allocations/lines/{lineId}', [LaborCostAllocationController::class, 'allocateLine']);

    // Forecasting
    Route::get('/forecasts', [WorkforceCostForecastController::class, 'index']);
    Route::post('/forecasts/generate', [WorkforceCostForecastController::class, 'generate']);

    // Variances
    Route::get('/variances', [WorkforceCostVarianceController::class, 'index']);
    Route::post('/variances/calculate', [WorkforceCostVarianceController::class, 'calculate']);

    // Economics
    Route::get('/economics', [WorkforceEconomicsController::class, 'index']);
    Route::post('/economics/calculate', [WorkforceEconomicsController::class, 'calculate']);

    // Scenarios
    Route::get('/scenarios', [WorkforceCostScenarioController::class, 'index']);
    Route::post('/scenarios', [WorkforceCostScenarioController::class, 'store']);

    // Reconciliation
    Route::get('/reconciliation', [WorkforceCostReconciliationController::class, 'index']);
    Route::post('/reconciliation/payroll', [WorkforceCostReconciliationController::class, 'reconcilePayroll']);

    // Advisory AI
    Route::get('/ai/insights', [AdvisoryWorkforceCostAiController::class, 'insights']);
});