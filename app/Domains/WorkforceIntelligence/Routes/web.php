<?php

use App\Domains\WorkforceIntelligence\Http\Controllers\WorkforceIntelligenceWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->prefix('workforce-intelligence')->group(function () {
    Route::get('/dashboard', [WorkforceIntelligenceWebController::class, 'dashboard'])->name('workforce-intelligence.dashboard');
});
