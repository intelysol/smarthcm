<?php

use App\Domains\Analytics\Http\Controllers\AnalyticsController;
use App\Domains\Analytics\Http\Controllers\HcmAiAnalyticsController;
use App\Domains\Analytics\Http\Controllers\HcmAnalyticsMetricController;
use App\Domains\Analytics\Http\Controllers\HcmDashboardController;
use App\Domains\Analytics\Http\Controllers\HcmDataQualityController;
use App\Domains\Analytics\Http\Controllers\HcmEngagementAndErAnalyticsController;
use App\Domains\Analytics\Http\Controllers\HcmFinancialAnalyticsController;
use App\Domains\Analytics\Http\Controllers\HcmOperationalAnalyticsController;
use App\Domains\Analytics\Http\Controllers\HcmReportBuilderController;
use App\Domains\Analytics\Http\Controllers\HcmWorkforceAnalyticsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/analytics')->name('api.v1.analytics.')->middleware(['web', 'auth', 'tenant'])->group(function (): void {
    // Legacy platform endpoints
    Route::get('kpis', [AnalyticsController::class, 'kpis']);
    Route::post('kpis', [AnalyticsController::class, 'createKpi']);
    Route::post('kpis/{kpi}/calculate', [AnalyticsController::class, 'calculate']);
    Route::get('kpis/{kpi}/values', [AnalyticsController::class, 'values']);
    Route::get('kpis/{kpi}/forecast', [AnalyticsController::class, 'forecast']);
    Route::get('dashboards', [AnalyticsController::class, 'dashboards']);
    Route::post('dashboards', [AnalyticsController::class, 'createDashboard']);
    Route::get('datasets', [AnalyticsController::class, 'datasets']);
    Route::post('datasets', [AnalyticsController::class, 'createDataset']);
    Route::get('reports', [AnalyticsController::class, 'reports']);
    Route::post('reports', [AnalyticsController::class, 'createReport']);
    Route::post('facts', [AnalyticsController::class, 'ingest']);
    Route::post('refresh', [AnalyticsController::class, 'refresh']);

    // Enterprise HCM Analytics Endpoints
    Route::prefix('hcm')->name('hcm.')->group(function () {
        Route::get('metrics', [HcmAnalyticsMetricController::class, 'index'])->name('metrics.index');
        Route::post('metrics', [HcmAnalyticsMetricController::class, 'store'])->name('metrics.store');
        Route::get('metrics/{metric}', [HcmAnalyticsMetricController::class, 'show'])->name('metrics.show');

        Route::get('workforce/headcount', [HcmWorkforceAnalyticsController::class, 'headcount'])->name('workforce.headcount');
        Route::get('workforce/turnover', [HcmWorkforceAnalyticsController::class, 'turnover'])->name('workforce.turnover');

        Route::get('operations/attendance', [HcmOperationalAnalyticsController::class, 'attendance'])->name('operations.attendance');
        Route::get('operations/recruitment', [HcmOperationalAnalyticsController::class, 'recruitment'])->name('operations.recruitment');

        Route::get('financial/payroll', [HcmFinancialAnalyticsController::class, 'payroll'])->name('financial.payroll');

        Route::get('engagement/scores', [HcmEngagementAndErAnalyticsController::class, 'engagement'])->name('engagement.scores');
        Route::get('er/aggregates', [HcmEngagementAndErAnalyticsController::class, 'erAggregates'])->name('er.aggregates');

        Route::post('reports/execute', [HcmReportBuilderController::class, 'execute'])->name('reports.execute');

        Route::get('dashboards/chro', [HcmDashboardController::class, 'chro'])->name('dashboards.chro');
        Route::get('dashboards/manager', [HcmDashboardController::class, 'manager'])->name('dashboards.manager');

        Route::post('ai/query', [HcmAiAnalyticsController::class, 'query'])->name('ai.query');
        Route::get('quality/summary', [HcmDataQualityController::class, 'index'])->name('quality.summary');
        Route::post('quality/evaluate', [HcmDataQualityController::class, 'evaluate'])->name('quality.evaluate');
    });
});
