<?php

use App\Domains\Analytics\Http\Controllers\HcmAiAnalyticsController;
use App\Domains\Analytics\Http\Controllers\HcmDashboardController;
use App\Domains\Analytics\Http\Controllers\HcmDataQualityController;
use App\Domains\Analytics\Http\Controllers\HcmFinancialAnalyticsController;
use App\Domains\Analytics\Http\Controllers\HcmOperationalAnalyticsController;
use App\Domains\Analytics\Http\Controllers\HcmReportBuilderController;
use App\Domains\Analytics\Http\Controllers\HcmWorkforceAnalyticsController;
use Illuminate\Support\Facades\Route;

Route::prefix('analytics/hcm')->name('hcm.analytics.')->middleware(['web', 'auth', 'tenant'])->group(function () {
    Route::get('/', [HcmDashboardController::class, 'chro'])->name('index');
    Route::get('chro', [HcmDashboardController::class, 'chro'])->name('chro');
    Route::get('manager', [HcmDashboardController::class, 'manager'])->name('manager');

    Route::get('workforce', [HcmWorkforceAnalyticsController::class, 'headcount'])->name('workforce');
    Route::get('attendance', [HcmOperationalAnalyticsController::class, 'attendance'])->name('attendance');
    Route::get('recruitment', [HcmOperationalAnalyticsController::class, 'recruitment'])->name('recruitment');
    Route::get('payroll', [HcmFinancialAnalyticsController::class, 'payroll'])->name('payroll');

    Route::get('reports', [HcmReportBuilderController::class, 'index'])->name('reports.index');
    Route::get('reports/builder', [HcmReportBuilderController::class, 'builder'])->name('reports.builder');
    Route::post('reports/execute', [HcmReportBuilderController::class, 'execute'])->name('reports.execute');

    Route::get('quality', [HcmDataQualityController::class, 'index'])->name('quality.index');
    Route::get('ai-assistant', [HcmAiAnalyticsController::class, 'index'])->name('ai.index');
});
