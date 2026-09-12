<?php

use App\Domains\EmployeeRelations\Http\Controllers\EmployeeRelationsUIController;
use Illuminate\Support\Facades\Route;

// Public anonymous intake & tracking
Route::prefix('hcm/employee-relations/anonymous')->group(function () {
    Route::get('/', [EmployeeRelationsUIController::class, 'anonymousIntake'])->name('er.anonymous.intake');
    Route::get('{token}', [EmployeeRelationsUIController::class, 'anonymousTracking'])->name('er.anonymous.tracking');
});

Route::middleware(['web', 'auth'])->prefix('hcm')->group(function () {

    // Employee Self-Service
    Route::prefix('me/employee-relations')->group(function () {
        Route::get('/', [EmployeeRelationsUIController::class, 'employeeDashboard'])->name('er.employee.dashboard');
        Route::get('report', [EmployeeRelationsUIController::class, 'employeeReport'])->name('er.employee.report');
        Route::get('{case}', [EmployeeRelationsUIController::class, 'employeeCaseDetail'])->name('er.employee.case');
    });

    // HR & Admin Portal
    Route::prefix('employee-relations')->group(function () {
        Route::get('/', [EmployeeRelationsUIController::class, 'adminDashboard'])->name('er.admin.dashboard');
        Route::get('cases', [EmployeeRelationsUIController::class, 'adminCases'])->name('er.admin.cases');
        Route::get('intake', [EmployeeRelationsUIController::class, 'adminIntake'])->name('er.admin.intake');
        Route::get('cases/{case}', [EmployeeRelationsUIController::class, 'adminCaseDetail'])->name('er.admin.case');
        Route::get('cases/{case}/investigation', [EmployeeRelationsUIController::class, 'adminInvestigation'])->name('er.admin.investigation');
        Route::get('cases/{case}/evidence', [EmployeeRelationsUIController::class, 'adminEvidence'])->name('er.admin.evidence');
        Route::get('cases/{case}/hearing', [EmployeeRelationsUIController::class, 'adminHearing'])->name('er.admin.hearing');
        Route::get('cases/{case}/decision', [EmployeeRelationsUIController::class, 'adminDecision'])->name('er.admin.decision');
    });
});
