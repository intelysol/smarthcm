<?php

use App\Domains\Payroll\Http\Controllers\CompensationController;
use App\Domains\Payroll\Http\Controllers\EmployeeCompensationController;
use App\Domains\Payroll\Http\Controllers\EmployeePayslipPortalController;
use App\Domains\Payroll\Http\Controllers\PaymentBatchController;
use App\Domains\Payroll\Http\Controllers\PayrollAccountingController;
use App\Domains\Payroll\Http\Controllers\PayrollAdjustmentController;
use App\Domains\Payroll\Http\Controllers\PayrollCalculationController;
use App\Domains\Payroll\Http\Controllers\PayrollInputController;
use App\Domains\Payroll\Http\Controllers\PayrollPeriodController;
use App\Domains\Payroll\Http\Controllers\PayrollReportController;
use App\Domains\Payroll\Http\Controllers\PayrollReviewController;
use App\Domains\Payroll\Http\Controllers\PayrollRunController;
use App\Domains\Payroll\Http\Controllers\PayslipController;
use App\Domains\Payroll\Http\Controllers\SalaryStructureController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/payroll')->middleware(['auth:sanctum'])->group(function (): void {
    // 1. Periods & Locking
    Route::get('/periods', [PayrollPeriodController::class, 'index']);
    Route::post('/periods', [PayrollPeriodController::class, 'store']);
    Route::get('/periods/{period}', [PayrollPeriodController::class, 'show']);
    Route::post('/periods/{period}/lock', [PayrollPeriodController::class, 'lock']);
    Route::post('/periods/{period}/reopen', [PayrollPeriodController::class, 'reopen']);

    // 2. Payroll Runs & Calculation
    Route::get('/runs', [PayrollRunController::class, 'index']);
    Route::post('/runs', [PayrollRunController::class, 'store']);
    Route::get('/runs/{run}', [PayrollRunController::class, 'show']);
    Route::post('/runs/{run}/calculate', [PayrollRunController::class, 'calculate']);
    Route::post('/runs/{run}/submit', [PayrollRunController::class, 'submit']);
    Route::post('/runs/{run}/approve', [PayrollRunController::class, 'approve']);
    Route::post('/runs/{run}/lock', [PayrollRunController::class, 'lock']);
    Route::get('/runs/{run}/review', [PayrollReviewController::class, 'review']);
    Route::get('/runs/{run}/report', [PayrollReportController::class, 'runReport']);

    // 3. Compensation Components & Structures
    Route::get('/components', [CompensationController::class, 'index']);
    Route::post('/components', [CompensationController::class, 'store']);
    Route::get('/structures', [SalaryStructureController::class, 'index']);
    Route::post('/structures', [SalaryStructureController::class, 'store']);

    // 4. Employee Compensation
    Route::get('/employees/{employee}/compensation', [EmployeeCompensationController::class, 'show']);
    Route::post('/employees/{employee}/compensation', [EmployeeCompensationController::class, 'store']);

    // 5. Inputs & Employee Calculation
    Route::post('/periods/{period}/employees/{employee}/inputs', [PayrollInputController::class, 'collect']);
    Route::post('/runs/{run}/employees/{employee}/calculate', [PayrollCalculationController::class, 'calculateEmployee']);

    // 6. Adjustments & Arrears
    Route::get('/adjustments', [PayrollAdjustmentController::class, 'index']);
    Route::post('/adjustments', [PayrollAdjustmentController::class, 'store']);
    Route::post('/adjustments/{adjustment}/approve', [PayrollAdjustmentController::class, 'approve']);
    Route::post('/adjustments/{adjustment}/reject', [PayrollAdjustmentController::class, 'reject']);

    // 7. Payslips
    Route::post('/runs/{run}/payslips/generate', [PayslipController::class, 'generate']);
    Route::post('/runs/{run}/payslips/publish', [PayslipController::class, 'publish']);
    Route::get('/payslips/{payslip}', [PayslipController::class, 'show']);
    Route::get('/my-payslips', [EmployeePayslipPortalController::class, 'myPayslips']);

    // 8. Payment Batches & Bank Exports
    Route::post('/payment-batches', [PaymentBatchController::class, 'store']);
    Route::get('/payment-batches/{batch}/export', [PaymentBatchController::class, 'export']);
    Route::post('/payment-batches/{batch}/submit', [PaymentBatchController::class, 'submit']);
    Route::post('/payment-batches/{batch}/mark-paid', [PaymentBatchController::class, 'markPaid']);

    // 9. Accounting Exports
    Route::post('/runs/{run}/accounting-export', [PayrollAccountingController::class, 'export']);
});
