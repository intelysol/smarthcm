<?php

use App\Domains\Performance\Http\Controllers\PerformanceAiController;
use App\Domains\Performance\Http\Controllers\PerformanceCalibrationController;
use App\Domains\Performance\Http\Controllers\PerformanceCheckinController;
use App\Domains\Performance\Http\Controllers\PerformanceCycleController;
use App\Domains\Performance\Http\Controllers\PerformanceFeedbackController;
use App\Domains\Performance\Http\Controllers\PerformanceGoalController;
use App\Domains\Performance\Http\Controllers\PerformanceReviewController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('v1/hcm')->group(function (): void {
    Route::prefix('performance')->group(function (): void {
        Route::get('cycles', [PerformanceCycleController::class, 'index']);
        Route::post('cycles', [PerformanceCycleController::class, 'store']);
        Route::get('cycles/{cycle}', [PerformanceCycleController::class, 'show']);
        Route::patch('cycles/{cycle}', [PerformanceCycleController::class, 'update']);
        Route::put('cycles/{cycle}/configuration', [PerformanceCycleController::class, 'configure']);
        Route::post('cycles/{cycle}/publish', [PerformanceCycleController::class, 'publish']);
        Route::post('cycles/{cycle}/transition', [PerformanceCycleController::class, 'transition']);

        Route::get('goals', [PerformanceGoalController::class, 'index']);
        Route::post('goals', [PerformanceGoalController::class, 'store']);
        Route::get('goals/{goal}', [PerformanceGoalController::class, 'show']);
        Route::patch('goals/{goal}', [PerformanceGoalController::class, 'update']);
        Route::post('goals/{goal}/progress', [PerformanceGoalController::class, 'progress']);

        Route::get('checkins', [PerformanceCheckinController::class, 'index']);
        Route::post('checkins', [PerformanceCheckinController::class, 'store']);
        Route::post('checkins/{checkin}/complete', [PerformanceCheckinController::class, 'complete']);

        Route::get('feedback', [PerformanceFeedbackController::class, 'index']);
        Route::post('feedback/requests', [PerformanceFeedbackController::class, 'requestFeedback']);
        Route::post('feedback/requests/{feedbackRequest}/respond', [PerformanceFeedbackController::class, 'submitResponse']);

        Route::post('reviews/{review}/self-assessment', [PerformanceReviewController::class, 'selfAssessment']);
        Route::post('reviews/{review}/manager-assessment', [PerformanceReviewController::class, 'managerAssessment']);

        Route::post('calibration/sessions', [PerformanceCalibrationController::class, 'createSession']);
        Route::post('calibration/sessions/{session}/adjust', [PerformanceCalibrationController::class, 'adjustRating']);
        Route::post('calibration/sessions/{session}/finalize', [PerformanceCalibrationController::class, 'finalize']);

        Route::get('pips', [\App\Domains\Performance\Http\Controllers\PerformancePipController::class, 'index']);
        Route::post('pips', [\App\Domains\Performance\Http\Controllers\PerformancePipController::class, 'store']);
        Route::get('pips/{pip}', [\App\Domains\Performance\Http\Controllers\PerformancePipController::class, 'show']);
        Route::post('pips/{pip}/actions', [\App\Domains\Performance\Http\Controllers\PerformancePipController::class, 'addAction']);
        Route::post('pips/{pip}/conclude', [\App\Domains\Performance\Http\Controllers\PerformancePipController::class, 'conclude']);

        Route::get('outcomes/{cycle}/{employee}', [\App\Domains\Performance\Http\Controllers\PerformanceOutcomeController::class, 'show']);
        Route::post('outcomes/publish', [\App\Domains\Performance\Http\Controllers\PerformanceOutcomeController::class, 'publish']);
        Route::post('outcomes/{outcome}/acknowledge', [\App\Domains\Performance\Http\Controllers\PerformanceOutcomeController::class, 'acknowledge']);
        Route::post('outcomes/appeals', [\App\Domains\Performance\Http\Controllers\PerformanceOutcomeController::class, 'submitAppeal']);
        Route::post('outcomes/appeals/{appeal}/resolve', [\App\Domains\Performance\Http\Controllers\PerformanceOutcomeController::class, 'resolveAppeal']);

        Route::get('competencies/frameworks', [\App\Domains\Performance\Http\Controllers\PerformanceCompetencyController::class, 'frameworks']);
        Route::post('reviews/{review}/competencies', [\App\Domains\Performance\Http\Controllers\PerformanceCompetencyController::class, 'recordAssessment']);
        Route::post('reviews/{review}/competencies/gaps', [\App\Domains\Performance\Http\Controllers\PerformanceCompetencyController::class, 'evaluateGaps']);

        Route::get('cycles/{cycle}/metrics', [\App\Domains\Performance\Http\Controllers\PerformanceReportingController::class, 'cycleMetrics']);

        Route::post('ai/smart-goal', [PerformanceAiController::class, 'draftSmartGoal']);
        Route::post('ai/summarize', [PerformanceAiController::class, 'summarize']);
    });

    Route::prefix('me/performance')->group(function (): void {
        Route::get('goals', [PerformanceGoalController::class, 'myIndex']);
        Route::post('goals', [PerformanceGoalController::class, 'store']);
        Route::get('goals/{goal}', [PerformanceGoalController::class, 'show']);
        Route::patch('goals/{goal}', [PerformanceGoalController::class, 'update']);
    });
});
