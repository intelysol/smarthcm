<?php

use App\Domains\Absence\Http\Controllers\AbsenceAnalyticsController;
use App\Domains\Absence\Http\Controllers\AbsenceCaseController;
use App\Domains\Absence\Http\Controllers\AbsenceEventController;
use App\Domains\Absence\Http\Controllers\AbsenceImpactController;
use App\Domains\Absence\Http\Controllers\AbsencePeriodController;
use App\Domains\Absence\Http\Controllers\AbsenceReconciliationController;
use App\Domains\Absence\Http\Controllers\AdvisoryAbsenceAiController;
use App\Domains\Absence\Http\Controllers\ReturnToWorkController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('v1/hcm/absence')->group(function () {
    // 1. Absence Events
    Route::get('events', [AbsenceEventController::class, 'index'])->name('api.absence.events.index');
    Route::post('events', [AbsenceEventController::class, 'store'])->name('api.absence.events.store');

    // 2. Absence Periods
    Route::get('periods', [AbsencePeriodController::class, 'index'])->name('api.absence.periods.index');
    Route::post('periods/{id}/extend', [AbsencePeriodController::class, 'extend'])->name('api.absence.periods.extend');
    Route::post('periods/{id}/return', [AbsencePeriodController::class, 'recordReturn'])->name('api.absence.periods.return');

    // 3. Operational Impacts & Replacement
    Route::get('impacts', [AbsenceImpactController::class, 'index'])->name('api.absence.impacts.index');
    Route::post('impacts/{id}/replace', [AbsenceImpactController::class, 'assignReplacement'])->name('api.absence.impacts.replace');

    // 4. Return-to-Work
    Route::get('return-to-work', [ReturnToWorkController::class, 'index'])->name('api.absence.return_to_work.index');
    Route::post('return-to-work', [ReturnToWorkController::class, 'store'])->name('api.absence.return_to_work.store');
    Route::post('return-to-work/{id}/progress', [ReturnToWorkController::class, 'progress'])->name('api.absence.return_to_work.progress');

    // 5. Absence Cases
    Route::get('cases', [AbsenceCaseController::class, 'index'])->name('api.absence.cases.index');
    Route::post('cases', [AbsenceCaseController::class, 'store'])->name('api.absence.cases.store');

    // 6. Reconciliation
    Route::get('reconciliation', [AbsenceReconciliationController::class, 'index'])->name('api.absence.reconciliation.index');
    Route::post('reconciliation/run', [AbsenceReconciliationController::class, 'run'])->name('api.absence.reconciliation.run');

    // 7. Analytics & Forecasting
    Route::get('analytics/rates', [AbsenceAnalyticsController::class, 'rates'])->name('api.absence.analytics.rates');
    Route::get('analytics/forecasts', [AbsenceAnalyticsController::class, 'forecasts'])->name('api.absence.analytics.forecasts');
    Route::post('analytics/forecasts/generate', [AbsenceAnalyticsController::class, 'generateForecast'])->name('api.absence.analytics.forecasts.generate');

    // 8. Advisory AI
    Route::get('ai/insights', [AdvisoryAbsenceAiController::class, 'insights'])->name('api.absence.ai.insights');
});