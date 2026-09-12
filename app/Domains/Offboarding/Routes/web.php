<?php

use App\Domains\Offboarding\Http\Controllers\SeparationDashboardController;
use App\Domains\Offboarding\Http\Controllers\SeparationSelfServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function (): void {
    // HR & Manager Offboarding & Separation Workspace
    Route::prefix('offboarding')->group(function (): void {
        Route::get('/', [SeparationDashboardController::class, 'index'])->name('offboarding.dashboard');
    });

    // Employee Self-Service & Post-Exit Portal
    Route::prefix('my/offboarding')->group(function (): void {
        Route::get('/portal', [SeparationSelfServiceController::class, 'employeeView'])->name('offboarding.employee.portal');
    });
});
