<?php

use App\Domains\Mobility\Http\Controllers\MobilityDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('hcm/mobility')->middleware(['web', 'auth'])->group(function () {
    Route::get('/', [MobilityDashboardController::class, 'index'])->name('mobility.dashboard');
    Route::get('/programs', [MobilityDashboardController::class, 'programs'])->name('mobility.programs.index');
    Route::get('/requests', [MobilityDashboardController::class, 'requests'])->name('mobility.requests.index');
    Route::get('/assignments', [MobilityDashboardController::class, 'assignments'])->name('mobility.assignments.index');
    Route::get('/relocation', [MobilityDashboardController::class, 'relocation'])->name('mobility.relocation.index');
    Route::get('/travelers', [MobilityDashboardController::class, 'businessTravelers'])->name('mobility.travelers.index');
});
