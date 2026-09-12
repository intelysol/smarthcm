<?php

use App\Domains\Organization\Http\Controllers\CompanyController;
use App\Domains\Organization\Http\Controllers\OrganizationChartController;
use App\Domains\Organization\Http\Controllers\OrganizationDashboardController;
use App\Domains\Organization\Http\Controllers\OrganizationEntityController;
use App\Domains\Organization\Http\Controllers\OrganizationReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::post('companies', [CompanyController::class, 'store'])
        ->name('companies.store');

    Route::prefix('organization')->name('organization.')->group(function (): void {
        Route::get('dashboard', OrganizationDashboardController::class)->name('dashboard');
        Route::get('chart', OrganizationChartController::class)->name('chart');
        Route::get('reports/summary', [OrganizationReportController::class, 'summary'])->name('reports.summary');
        Route::get('reports/departments', [OrganizationReportController::class, 'departments'])->name('reports.departments');
        Route::post('{entity}/import', [OrganizationEntityController::class, 'import'])->name('entities.import');
        Route::get('{entity}/export', [OrganizationEntityController::class, 'export'])->name('entities.export');
        Route::get('{entity}', [OrganizationEntityController::class, 'index'])->name('entities.index');
        Route::post('{entity}', [OrganizationEntityController::class, 'store'])->name('entities.store');
        Route::get('{entity}/{id}', [OrganizationEntityController::class, 'show'])->name('entities.show');
        Route::match(['put', 'patch'], '{entity}/{id}', [OrganizationEntityController::class, 'update'])->name('entities.update');
        Route::delete('{entity}/{id}', [OrganizationEntityController::class, 'destroy'])->name('entities.destroy');
    });
});
