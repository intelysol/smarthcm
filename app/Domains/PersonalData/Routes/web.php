<?php

use App\Domains\PersonalData\Http\Controllers\PersonalDataDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/hcm/personal-data/governance', [PersonalDataDashboardController::class, 'governanceDashboard'])
        ->name('personal-data.governance');

    Route::get('/hcm/personal-data/portal/{employeeId}', [PersonalDataDashboardController::class, 'employeePortal'])
        ->name('personal-data.portal');

    Route::get('/hcm/personal-data/change-requests', [PersonalDataDashboardController::class, 'changeRequests'])
        ->name('personal-data.change-requests');
});
