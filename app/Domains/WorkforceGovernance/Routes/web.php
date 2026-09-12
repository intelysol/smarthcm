<?php

use App\Domains\WorkforceGovernance\Http\Controllers\WorkforceDataGovernanceWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->prefix('workforce-governance')->group(function () {
    Route::get('/dashboard', [WorkforceDataGovernanceWebController::class, 'dashboard'])->name('workforce-governance.dashboard');
});
