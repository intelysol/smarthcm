<?php

use App\Domains\EmployeeExperience\Http\Controllers\EmployeeExperienceWebController;
use App\Domains\EmployeeExperience\Http\Controllers\ManagerWorkbenchWebController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal')->middleware(['web'])->group(function () {
    Route::get('/', [EmployeeExperienceWebController::class, 'dashboard'])->name('portal.dashboard');
    Route::get('/dashboard', [EmployeeExperienceWebController::class, 'dashboard']);
    Route::get('/work', [EmployeeExperienceWebController::class, 'work'])->name('portal.work');
    Route::get('/requests', [EmployeeExperienceWebController::class, 'requests'])->name('portal.requests');
    Route::get('/profile', [EmployeeExperienceWebController::class, 'profile'])->name('portal.profile');
    Route::get('/pay', [EmployeeExperienceWebController::class, 'pay'])->name('portal.pay');
    Route::get('/growth', [EmployeeExperienceWebController::class, 'growth'])->name('portal.growth');
    Route::get('/documents', [EmployeeExperienceWebController::class, 'documents'])->name('portal.documents');
    Route::get('/services', [EmployeeExperienceWebController::class, 'services'])->name('portal.services');
    Route::get('/directory', [EmployeeExperienceWebController::class, 'directory'])->name('portal.directory');
    Route::get('/privacy', [EmployeeExperienceWebController::class, 'privacy'])->name('portal.privacy');

    // Manager Workbench Web Routes
    Route::prefix('manager')->group(function () {
        Route::get('/workbench', [ManagerWorkbenchWebController::class, 'workbench'])->name('portal.manager.workbench');
        Route::get('/approvals', [ManagerWorkbenchWebController::class, 'approvals'])->name('portal.manager.approvals');
    });
});
