<?php

use App\Domains\WorkforceOptimization\Http\Controllers\AdvisoryOptimizationAiController;
use App\Domains\WorkforceOptimization\Http\Controllers\OptimizationDashboardController;
use App\Domains\WorkforceOptimization\Http\Controllers\OptimizationModelController;
use App\Domains\WorkforceOptimization\Http\Controllers\OptimizationOpportunityController;
use App\Domains\WorkforceOptimization\Http\Controllers\OptimizationOutcomeController;
use App\Domains\WorkforceOptimization\Http\Controllers\OptimizationRecommendationController;
use App\Domains\WorkforceOptimization\Http\Controllers\OptimizationRunController;
use App\Domains\WorkforceOptimization\Http\Controllers\OptimizationScenarioController;
use App\Domains\WorkforceOptimization\Http\Controllers\SkillsOptimizationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/workforce-optimization')->group(function () {
    // Dashboard
    Route::get('/dashboard', [OptimizationDashboardController::class, 'index']);

    // Models
    Route::get('/models', [OptimizationModelController::class, 'index']);
    Route::post('/models', [OptimizationModelController::class, 'store']);

    // Optimization Runs
    Route::get('/runs', [OptimizationRunController::class, 'index']);
    Route::post('/runs', [OptimizationRunController::class, 'store']);
    Route::get('/runs/{id}', [OptimizationRunController::class, 'show']);

    // Opportunities
    Route::get('/opportunities', [OptimizationOpportunityController::class, 'index']);
    Route::post('/opportunities/detect', [OptimizationOpportunityController::class, 'detect']);

    // Recommendations
    Route::get('/recommendations', [OptimizationRecommendationController::class, 'index']);
    Route::get('/recommendations/{id}', [OptimizationRecommendationController::class, 'show']);
    Route::post('/recommendations/{id}/approve', [OptimizationRecommendationController::class, 'approve']);
    Route::post('/recommendations/{id}/reject', [OptimizationRecommendationController::class, 'reject']);

    // Scenarios (What-if & Pareto comparisons)
    Route::get('/scenarios', [OptimizationScenarioController::class, 'index']);
    Route::post('/scenarios', [OptimizationScenarioController::class, 'store']);
    Route::post('/scenarios/compare', [OptimizationScenarioController::class, 'compare']);

    // Skills & SPOF Optimization
    Route::get('/skills/analysis', [SkillsOptimizationController::class, 'analyze']);
    Route::get('/skills/spofs', [SkillsOptimizationController::class, 'spofs']);

    // Outcomes & Realization Tracking
    Route::get('/outcomes', [OptimizationOutcomeController::class, 'index']);
    Route::post('/outcomes', [OptimizationOutcomeController::class, 'store']);

    // Advisory AI Explanations
    Route::get('/ai/explain/{id}', [AdvisoryOptimizationAiController::class, 'explain']);
});
