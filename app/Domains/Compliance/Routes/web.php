<?php

use App\Domains\Compliance\Http\Controllers\ComplianceDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HCM Compliance Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth'])->prefix('hcm/compliance')->name('compliance.')->group(function () {
    Route::get('/', [ComplianceDashboardController::class, 'webDashboard'])->name('dashboard');
    Route::get('/employees/{employeeId}', [ComplianceDashboardController::class, 'webEmployeeProfile'])->name('employee.profile');
    Route::get('/expirations', [ComplianceDashboardController::class, 'webExpirations'])->name('expirations');
    Route::get('/exemptions', [ComplianceDashboardController::class, 'webExemptions'])->name('exemptions');
});
