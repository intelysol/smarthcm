<?php

use App\Domains\Onboarding\Http\Controllers\OnboardingDashboardController;
use App\Domains\Onboarding\Http\Controllers\OnboardingEmployeePortalController;
use Illuminate\Support\Facades\Route;

// HR & Manager Onboarding Workspace & Dashboard with Public Marketing Fallback
Route::prefix('onboarding')->middleware(['web'])->group(function (): void {
    Route::get('/', function (\Illuminate\Http\Request $request) {
        if (auth()->check()) {
            return app(OnboardingDashboardController::class)->index($request);
        }
        return app(\App\Domains\PublicWebsite\Http\Controllers\PublicWebsiteController::class)->feature($request, 'onboarding');
    })->name('onboarding.public');

    Route::middleware(['auth'])->group(function (): void {
        Route::get('/dashboard', [OnboardingDashboardController::class, 'index'])->name('onboarding.dashboard');
    });
});

Route::middleware(['web', 'auth'])->group(function (): void {
    // Employee Self-Service Preboarding & Onboarding Portal
    Route::prefix('my/onboarding')->group(function (): void {
        Route::get('/', [OnboardingEmployeePortalController::class, 'portalWeb'])->name('onboarding.portal');
    });
});
