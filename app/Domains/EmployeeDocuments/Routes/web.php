<?php

use App\Domains\EmployeeDocuments\Http\Controllers\EmployeeDocumentDashboardController;
use App\Domains\EmployeeDocuments\Http\Controllers\EmployeeDocumentSelfServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function (): void {
    // HR Document Center & Personnel Files
    Route::prefix('hcm/documents')->group(function (): void {
        Route::get('/', [EmployeeDocumentDashboardController::class, 'index'])->name('employee_documents.dashboard');
        Route::get('personnel-file/{employee}', [EmployeeDocumentDashboardController::class, 'personnelFile'])->name('employee_documents.personnel_file');
    });

    // Employee Self-Service Document Portal
    Route::prefix('my/documents')->group(function (): void {
        Route::get('/portal', [EmployeeDocumentSelfServiceController::class, 'portalView'])->name('employee_documents.employee.portal');
    });
});
