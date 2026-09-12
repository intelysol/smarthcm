<?php

use App\Domains\WorkforceAdmin\Http\Controllers\WorkforceAdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('hcm/workforce-admin')->middleware(['web', 'auth'])->group(function () {
    Route::get('/', [WorkforceAdminDashboardController::class, 'index'])->name('workforce_admin.dashboard');
    Route::get('/queues', [WorkforceAdminDashboardController::class, 'queues'])->name('workforce_admin.queues.index');
    Route::get('/exceptions', [WorkforceAdminDashboardController::class, 'exceptions'])->name('workforce_admin.exceptions.index');
    Route::get('/bulk', [WorkforceAdminDashboardController::class, 'bulk'])->name('workforce_admin.bulk.index');
    Route::get('/data-quality', [WorkforceAdminDashboardController::class, 'dataQuality'])->name('workforce_admin.data_quality.index');
    Route::get('/calendar', [WorkforceAdminDashboardController::class, 'calendar'])->name('workforce_admin.calendar.index');
    Route::get('/checklists', [WorkforceAdminDashboardController::class, 'checklists'])->name('workforce_admin.checklists.index');
    Route::get('/changes', [WorkforceAdminDashboardController::class, 'changes'])->name('workforce_admin.changes.index');
});
