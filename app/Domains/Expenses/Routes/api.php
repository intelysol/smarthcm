<?php

use App\Domains\Expenses\Http\Controllers\CorporateCardController;
use App\Domains\Expenses\Http\Controllers\ExpenseClaimController;
use App\Domains\Expenses\Http\Controllers\ExpenseDashboardController;
use App\Domains\Expenses\Http\Controllers\ExpensePolicyController;
use App\Domains\Expenses\Http\Controllers\ExpenseReimbursementController;
use App\Domains\Expenses\Http\Controllers\ExpenseReportController;
use App\Domains\Expenses\Http\Controllers\TravelAdvanceController;
use App\Domains\Expenses\Http\Controllers\TravelRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/hcm/expenses')->middleware(['api'])->group(function () {
    // Dashboard & Summary
    Route::get('/', [ExpenseDashboardController::class, 'index'])->name('api.expenses.dashboard');
    Route::get('/summary', [ExpenseReportController::class, 'summary'])->name('api.expenses.summary');
    Route::get('/reports/by-department', [ExpenseReportController::class, 'byDepartment'])->name('api.expenses.reports.department');
    Route::get('/reports/by-category', [ExpenseReportController::class, 'byCategory'])->name('api.expenses.reports.category');
    Route::post('/accounting/export', [ExpenseReportController::class, 'exportGl'])->name('api.expenses.accounting.export');

    // Expense Policies
    Route::get('/policies', [ExpensePolicyController::class, 'index'])->name('api.expenses.policies.index');
    Route::post('/policies', [ExpensePolicyController::class, 'store'])->name('api.expenses.policies.store');

    // Travel Requests
    Route::get('/travel', [TravelRequestController::class, 'index'])->name('api.expenses.travel.index');
    Route::post('/travel', [TravelRequestController::class, 'store'])->name('api.expenses.travel.store');
    Route::post('/travel/{travelRequest}/submit', [TravelRequestController::class, 'submit'])->name('api.expenses.travel.submit');
    Route::post('/travel/{travelRequest}/approve', [TravelRequestController::class, 'approve'])->name('api.expenses.travel.approve');
    Route::post('/travel/{travelRequest}/reject', [TravelRequestController::class, 'reject'])->name('api.expenses.travel.reject');

    // Travel Advances
    Route::get('/travel/advances', [TravelAdvanceController::class, 'index'])->name('api.expenses.advances.index');
    Route::post('/travel/advances', [TravelAdvanceController::class, 'store'])->name('api.expenses.advances.store');
    Route::post('/travel/advances/{travelAdvance}/approve', [TravelAdvanceController::class, 'approve'])->name('api.expenses.advances.approve');
    Route::post('/travel/advances/{travelAdvance}/disburse', [TravelAdvanceController::class, 'disburse'])->name('api.expenses.advances.disburse');

    // Expense Claims
    Route::get('/claims', [ExpenseClaimController::class, 'index'])->name('api.expenses.claims.index');
    Route::post('/claims', [ExpenseClaimController::class, 'store'])->name('api.expenses.claims.store');
    Route::get('/claims/{claim}', [ExpenseClaimController::class, 'show'])->name('api.expenses.claims.show');
    Route::post('/claims/{claim}/lines', [ExpenseClaimController::class, 'addLine'])->name('api.expenses.claims.lines.store');
    Route::post('/claims/{claim}/submit', [ExpenseClaimController::class, 'submit'])->name('api.expenses.claims.submit');
    Route::post('/claims/{claim}/approve', [ExpenseClaimController::class, 'approve'])->name('api.expenses.claims.approve');
    Route::post('/claims/{claim}/finance-approve', [ExpenseClaimController::class, 'financeApprove'])->name('api.expenses.claims.finance-approve');
    Route::post('/claim-lines/{line}/override', [ExpenseClaimController::class, 'overridePolicy'])->name('api.expenses.claims.lines.override');

    // Corporate Cards
    Route::get('/corporate-cards', [CorporateCardController::class, 'index'])->name('api.expenses.corporate-cards.index');
    Route::post('/corporate-cards', [CorporateCardController::class, 'store'])->name('api.expenses.corporate-cards.store');
    Route::post('/corporate-cards/transactions/{transaction}/match', [CorporateCardController::class, 'match'])->name('api.expenses.corporate-cards.match');

    // Reimbursements
    Route::get('/reimbursements', [ExpenseReimbursementController::class, 'index'])->name('api.expenses.reimbursements.index');
    Route::post('/reimbursements', [ExpenseReimbursementController::class, 'store'])->name('api.expenses.reimbursements.store');
    Route::post('/reimbursements/{expenseReimbursement}/approve', [ExpenseReimbursementController::class, 'approve'])->name('api.expenses.reimbursements.approve');
    Route::post('/reimbursements/{expenseReimbursement}/pay', [ExpenseReimbursementController::class, 'pay'])->name('api.expenses.reimbursements.pay');
});
