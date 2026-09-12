<?php

use App\Domains\WorkforceProductivity\Http\Controllers\AdvisoryProductivityAiController;
use App\Domains\WorkforceProductivity\Http\Controllers\LaborEfficiencyController;
use App\Domains\WorkforceProductivity\Http\Controllers\ProductivityBenchmarkController;
use App\Domains\WorkforceProductivity\Http\Controllers\ProductivityExplorerController;
use App\Domains\WorkforceProductivity\Http\Controllers\ProductivityForecastController;
use App\Domains\WorkforceProductivity\Http\Controllers\ProductivityImpactController;
use App\Domains\WorkforceProductivity\Http\Controllers\ProductivityMeasurementController;
use App\Domains\WorkforceProductivity\Http\Controllers\ProductivityMetricController;
use App\Domains\WorkforceProductivity\Http\Controllers\ProductivityScenarioController;
use App\Domains\WorkforceProductivity\Http\Controllers\ProductivitySummaryController;
use App\Domains\WorkforceProductivity\Http\Controllers\ProductivityUtilizationController;
use App\Domains\WorkforceProductivity\Http\Controllers\WorkforceRoiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/hcm/productivity')->middleware(['api'])->group(function () {
    // Executive Summary
    Route::get('/summary', [ProductivitySummaryController::class, 'summary']);

    // Metrics & Versions
    Route::get('/metrics', [ProductivityMetricController::class, 'index']);
    Route::post('/metrics', [ProductivityMetricController::class, 'store']);
    Route::post('/metrics/{id}/versions', [ProductivityMetricController::class, 'createVersion']);

    // Operational Output & Productive Time Ingestion
    Route::post('/output', [ProductivityMeasurementController::class, 'recordOutput']);
    Route::post('/time', [ProductivityMeasurementController::class, 'recordTime']);

    // Measurements
    Route::get('/measurements', [ProductivityMeasurementController::class, 'index']);
    Route::post('/measurements', [ProductivityMeasurementController::class, 'store']);

    // Utilization & Schedule Effectiveness
    Route::get('/utilization', [ProductivityUtilizationController::class, 'utilization']);

    // Labor Efficiency
    Route::get('/labor-efficiency', [LaborEfficiencyController::class, 'efficiency']);

    // Operational Impacts (Overtime, Absence, Turnover)
    Route::get('/overtime', [ProductivityImpactController::class, 'overtime']);
    Route::get('/absence-impact', [ProductivityImpactController::class, 'absenceImpact']);
    Route::get('/turnover-impact', [ProductivityImpactController::class, 'turnoverImpact']);

    // Forecasting
    Route::get('/forecast', [ProductivityForecastController::class, 'index']);
    Route::post('/forecast', [ProductivityForecastController::class, 'store']);

    // Workforce ROI
    Route::get('/roi', [WorkforceRoiController::class, 'index']);
    Route::post('/roi/training', [WorkforceRoiController::class, 'storeTrainingRoi']);
    Route::post('/roi/recruitment', [WorkforceRoiController::class, 'storeRecruitmentRoi']);

    // Scenarios
    Route::get('/scenarios', [ProductivityScenarioController::class, 'index']);
    Route::post('/scenarios', [ProductivityScenarioController::class, 'store']);

    // Benchmarking & Normalized Index
    Route::get('/benchmarks', [ProductivityBenchmarkController::class, 'index']);
    Route::post('/benchmarks', [ProductivityBenchmarkController::class, 'store']);

    // Explorer & Export
    Route::get('/explorer', [ProductivityExplorerController::class, 'explorer']);
    Route::get('/export/csv', [ProductivityExplorerController::class, 'exportCsv']);

    // Advisory AI
    Route::get('/ai/insights', [AdvisoryProductivityAiController::class, 'insights']);
});
