<?php

use App\Domains\Benefits\Http\Controllers\BenefitsDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('hcm/benefits')->middleware(['web', 'auth'])->group(function () {
    Route::get('/', [BenefitsDashboardController::class, 'index'])->name('benefits.dashboard');
    Route::get('/programs', [BenefitsDashboardController::class, 'programs'])->name('benefits.programs.index');
    Route::get('/plans', [BenefitsDashboardController::class, 'plans'])->name('benefits.plans.index');
    Route::get('/open-enrollment', [BenefitsDashboardController::class, 'openEnrollment'])->name('benefits.open_enrollment.index');
    Route::get('/self-service', [BenefitsDashboardController::class, 'selfService'])->name('benefits.self_service.wizard');
    Route::get('/enrollments', [BenefitsDashboardController::class, 'enrollments'])->name('benefits.enrollments.index');
    Route::get('/life-events', [BenefitsDashboardController::class, 'lifeEvents'])->name('benefits.life_events.index');
    Route::get('/reconciliation', [BenefitsDashboardController::class, 'reconciliation'])->name('benefits.reconciliation.index');
    Route::get('/claims', [BenefitsDashboardController::class, 'claims'])->name('benefits.claims.index');
    Route::get('/loans', [BenefitsDashboardController::class, 'loans'])->name('benefits.loans.index');
    Route::get('/loans/{loan}', [BenefitsDashboardController::class, 'showLoan'])->name('benefits.loans.show');
});
