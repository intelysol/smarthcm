<?php

use App\Domains\WorkforceOptimization\Http\Controllers\OptimizationDashboardController;
use App\Domains\WorkforceOptimization\Http\Controllers\OptimizationOpportunityController;
use App\Domains\WorkforceOptimization\Http\Controllers\OptimizationOutcomeController;
use App\Domains\WorkforceOptimization\Http\Controllers\OptimizationRecommendationController;
use App\Domains\WorkforceOptimization\Http\Controllers\OptimizationScenarioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->prefix('workforce-optimization')->name('workforce_optimization.')->group(function () {
    Route::get('/', [OptimizationDashboardController::class, 'index'])->name('dashboard');
    Route::get('/opportunities', [OptimizationOpportunityController::class, 'index'])->name('opportunities');
    Route::get('/recommendations', [OptimizationRecommendationController::class, 'index'])->name('recommendations');
    Route::get('/scenarios', [OptimizationScenarioController::class, 'index'])->name('scenarios');
    Route::get('/outcomes', [OptimizationOutcomeController::class, 'index'])->name('outcomes');
});
