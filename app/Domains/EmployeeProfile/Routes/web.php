<?php

use App\Domains\EmployeeProfile\Http\Controllers\EmployeeDirectoryDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->prefix('hcm/workforce')->group(function () {
    Route::get('directory', [EmployeeDirectoryDashboardController::class, 'directoryView'])->name('employee_profile.directory');
    Route::get('org-chart', [EmployeeDirectoryDashboardController::class, 'orgChartView'])->name('employee_profile.org_chart');
    Route::get('profile/{id}', [EmployeeDirectoryDashboardController::class, 'profileView'])->name('employee_profile.profile');
});
