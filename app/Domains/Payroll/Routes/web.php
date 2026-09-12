<?php

use App\Domains\Payroll\Http\Controllers\PayrollUIController;
use Illuminate\Support\Facades\Route;

Route::prefix('payroll')->middleware(['web', 'auth'])->group(function (): void {
    Route::get('/', [PayrollUIController::class, 'dashboard'])->name('payroll.dashboard');
    Route::get('/periods', [PayrollUIController::class, 'periods'])->name('payroll.periods.index');
    Route::get('/runs', [PayrollUIController::class, 'runs'])->name('payroll.runs.index');
    Route::get('/runs/{run}', [PayrollUIController::class, 'runDetails'])->name('payroll.runs.show');
    Route::get('/structures', [PayrollUIController::class, 'structures'])->name('payroll.structures.index');
    Route::get('/adjustments', [PayrollUIController::class, 'adjustments'])->name('payroll.adjustments.index');
    Route::get('/payslips', [PayrollUIController::class, 'payslips'])->name('payroll.payslips.index');
    Route::get('/payslips/{payslip}', [PayrollUIController::class, 'payslipShow'])->name('payroll.payslips.show');
    Route::get('/payments', [PayrollUIController::class, 'paymentBatches'])->name('payroll.payments.index');
    Route::get('/reports', [PayrollUIController::class, 'reports'])->name('payroll.reports.index');
    Route::get('/settings', [PayrollUIController::class, 'settings'])->name('payroll.settings.index');
});
