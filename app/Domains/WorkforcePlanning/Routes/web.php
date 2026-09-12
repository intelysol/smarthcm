<?php

use App\Domains\WorkforcePlanning\Http\Controllers\WorkforcePlanningDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('workforce-planning')->middleware(['web'])->group(function () {
    Route::get('/', [WorkforcePlanningDashboardController::class, 'webDashboard'])->name('workforce-planning.dashboard');
});
