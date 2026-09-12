<?php

use App\Domains\Compensation\Http\Controllers\CompensationAiController;
use App\Domains\Compensation\Http\Controllers\CompensationAnalyticsController;
use App\Domains\Compensation\Http\Controllers\CompensationBudgetController;
use App\Domains\Compensation\Http\Controllers\CompensationCalibrationController;
use App\Domains\Compensation\Http\Controllers\CompensationCycleController;
use App\Domains\Compensation\Http\Controllers\CompensationPayrollIntegrationController;
use App\Domains\Compensation\Http\Controllers\CompensationRecommendationController;
use App\Domains\Compensation\Http\Controllers\TotalRewardsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('v1/hcm/compensation')->group(function (): void {
    // Cycles
    Route::get('cycles', [CompensationCycleController::class, 'index']);
    Route::post('cycles', [CompensationCycleController::class, 'store']);
    Route::get('cycles/{cycle}', [CompensationCycleController::class, 'show']);
    Route::put('cycles/{cycle}/configuration', [CompensationCycleController::class, 'configure']);
    Route::post('cycles/{cycle}/transition', [CompensationCycleController::class, 'transition']);

    // Budgets
    Route::get('cycles/{cycle}/budgets', [CompensationBudgetController::class, 'index']);
    Route::post('cycles/{cycle}/budgets', [CompensationBudgetController::class, 'allocate']);

    // Recommendations (Merit / Promotion / Market Adjustments)
    Route::get('cycles/{cycle}/recommendations', [CompensationRecommendationController::class, 'index']);
    Route::post('cycles/{cycle}/recommendations/{employee}/propose', [CompensationRecommendationController::class, 'propose']);
    Route::post('recommendations/{recommendation}/approve', [CompensationRecommendationController::class, 'approve']);

    // Calibration
    Route::get('cycles/{cycle}/calibration/sessions', [CompensationCalibrationController::class, 'index']);
    Route::post('cycles/{cycle}/calibration/sessions', [CompensationCalibrationController::class, 'createSession']);
    Route::post('calibration/sessions/{session}/adjust/{recommendation}', [CompensationCalibrationController::class, 'adjust']);
    Route::post('calibration/sessions/{session}/complete', [CompensationCalibrationController::class, 'complete']);

    // Payroll Handoff / Integration
    Route::post('cycles/{cycle}/export-payroll', [CompensationPayrollIntegrationController::class, 'export']);

    // Total Rewards Statements
    Route::get('employees/{employee}/total-rewards/{year}', [TotalRewardsController::class, 'show']);
    Route::post('employees/{employee}/total-rewards', [TotalRewardsController::class, 'generate']);

    // Pay Equity Analytics
    Route::get('cycles/{cycle}/analytics/equity', [CompensationAnalyticsController::class, 'payEquity']);

    // AI Advisory
    Route::post('ai/draft-justification', [CompensationAiController::class, 'draftJustification']);
});
