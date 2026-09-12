<?php

use App\Domains\Expenses\Http\Controllers\CorporateCardWebController;
use App\Domains\Expenses\Http\Controllers\ExpenseAccountingWebController;
use App\Domains\Expenses\Http\Controllers\ExpenseAiAdvisorWebController;
use App\Domains\Expenses\Http\Controllers\ExpenseClaimController;
use App\Domains\Expenses\Http\Controllers\ExpenseDashboardController;
use App\Domains\Expenses\Http\Controllers\ExpensePolicyWebController;
use App\Domains\Expenses\Http\Controllers\ExpenseReimbursementController;
use App\Domains\Expenses\Http\Controllers\TravelAdvanceController;
use App\Domains\Expenses\Http\Controllers\TravelRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('expenses')->middleware(['web'])->group(function () {
    Route::get('/', [ExpenseDashboardController::class, 'index'])->name('expenses.dashboard');
    Route::get('/travel', [TravelRequestController::class, 'index'])->name('expenses.travel.index');
    Route::get('/advances', [TravelAdvanceController::class, 'index'])->name('expenses.advances.index');
    Route::get('/claims', [ExpenseClaimController::class, 'index'])->name('expenses.claims.index');
    Route::get('/claims/{claim}', [ExpenseClaimController::class, 'show'])->name('expenses.claims.show');
    Route::get('/reimbursements', [ExpenseReimbursementController::class, 'index'])->name('expenses.reimbursements.index');

    // Policies
    Route::get('/policies', [ExpensePolicyWebController::class, 'index'])->name('expenses.policies.index');

    // Corporate Cards
    Route::get('/cards', [CorporateCardWebController::class, 'index'])->name('expenses.cards.index');

    // Accounting & GL Exports
    Route::get('/accounting', [ExpenseAccountingWebController::class, 'index'])->name('expenses.accounting.index');
    Route::post('/accounting/export', [ExpenseAccountingWebController::class, 'export'])->name('expenses.accounting.export');
    Route::post('/accounting/lock', [ExpenseAccountingWebController::class, 'lockPeriod'])->name('expenses.accounting.lock');

    // Advisory AI Portal
    Route::get('/ai-advisor', [ExpenseAiAdvisorWebController::class, 'index'])->name('expenses.ai.advisor');
    Route::post('/ai-advisor/query', [ExpenseAiAdvisorWebController::class, 'query'])->name('expenses.ai.query');
});

