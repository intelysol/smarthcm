<?php

use App\Domains\Onboarding\Http\Controllers\OnboardingDashboardController;
use App\Domains\Onboarding\Http\Controllers\OnboardingEmployeePortalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function (): void {
    // HR & Manager Onboarding Workspace & Dashboard
    Route::prefix('onboarding')->group(function (): void {
        Route::get('/', [OnboardingDashboardController::class, 'index'])->name('onboarding.dashboard');
    });

    // Employee Self-Service Preboarding & Onboarding Portal
    Route::prefix('my/onboarding')->group(function (): void {
        Route::get('/', [OnboardingEmployeePortalController::class, 'portalWeb'])->name('onboarding.portal');
    });
});
