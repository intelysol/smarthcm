<?php

use App\Domains\Lifecycle\Http\Controllers\PersonnelActionDashboardController;
use App\Domains\Lifecycle\Http\Controllers\PersonnelActionSelfServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function (): void {
    // HR & Manager Personnel Actions Workspace
    Route::prefix('lifecycle')->group(function (): void {
        Route::get('/', [PersonnelActionDashboardController::class, 'index'])->name('lifecycle.dashboard');
    });

    // Employee Self-Service Personnel Actions
    Route::prefix('my/lifecycle')->group(function (): void {
        Route::get('/actions', [PersonnelActionSelfServiceController::class, 'employeeView'])->name('lifecycle.employee.actions');
    });
});
