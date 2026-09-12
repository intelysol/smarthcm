<?php

use App\Domains\Employee\Http\Controllers\EmployeeController;
use App\Domains\Employee\Http\Controllers\EmployeeCustomFieldController;
use App\Domains\Employee\Http\Controllers\EmployeeDashboardController;
use App\Domains\Employee\Http\Controllers\EmployeeReportController;
use App\Domains\Employee\Http\Controllers\EmployeeSectionController;
use App\Domains\Employee\Http\Controllers\HcmReferenceController;
use App\Domains\Employee\Http\Controllers\EmployeeLifecycleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('employees')->name('employees.')->group(function (): void {
    Route::get('hcm/dashboard', [HcmReferenceController::class, 'dashboard'])->name('hcm.dashboard');
    Route::get('hcm/leave', [HcmReferenceController::class, 'leave'])->name('hcm.leave');
    Route::get('hcm/payroll', [HcmReferenceController::class, 'payroll'])->name('hcm.payroll');
    Route::post('{employee}/lifecycle', EmployeeLifecycleController::class)->name('lifecycle');
    Route::get('dashboard', EmployeeDashboardController::class)->name('dashboard');
    Route::get('reports/master-list', [EmployeeReportController::class, 'masterList'])->name('reports.master-list');
    Route::get('custom-fields', [EmployeeCustomFieldController::class, 'index'])->name('custom-fields.index');
    Route::post('custom-fields', [EmployeeCustomFieldController::class, 'store'])->name('custom-fields.store');
    Route::match(['put', 'patch'], 'custom-fields/{definition}', [EmployeeCustomFieldController::class, 'update'])->name('custom-fields.update');
    Route::delete('custom-fields/{definition}', [EmployeeCustomFieldController::class, 'destroy'])->name('custom-fields.destroy');
    Route::put('{employee}/custom-fields/{definition}', [EmployeeCustomFieldController::class, 'setValue'])->name('custom-fields.values.set');
    Route::get('{employee}/{section}', [EmployeeSectionController::class, 'index'])->name('sections.index');
    Route::post('{employee}/{section}', [EmployeeSectionController::class, 'store'])->name('sections.store');
    Route::match(['put', 'patch'], '{employee}/{section}/{id}', [EmployeeSectionController::class, 'update'])->name('sections.update');
    Route::delete('{employee}/{section}/{id}', [EmployeeSectionController::class, 'destroy'])->name('sections.destroy');
    Route::get('/', [EmployeeController::class, 'index'])->name('index');
    Route::post('/', [EmployeeController::class, 'store'])->name('store');
    Route::get('{employee}', [EmployeeController::class, 'show'])->name('show');
    Route::match(['put', 'patch'], '{employee}', [EmployeeController::class, 'update'])->name('update');
    Route::delete('{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
});
