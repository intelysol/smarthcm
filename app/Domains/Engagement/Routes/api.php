<?php

use App\Domains\Engagement\Http\Controllers\AdminActionPlanController;
use App\Domains\Engagement\Http\Controllers\AdminCultureController;
use App\Domains\Engagement\Http\Controllers\AdminEngagementCampaignController;
use App\Domains\Engagement\Http\Controllers\AdminEngagementReportController;
use App\Domains\Engagement\Http\Controllers\AdminEngagementResultController;
use App\Domains\Engagement\Http\Controllers\AdminEngagementSurveyController;
use App\Domains\Engagement\Http\Controllers\AdminRecognitionController;
use App\Domains\Engagement\Http\Controllers\AdminSuggestionController;
use App\Domains\Engagement\Http\Controllers\EmployeeEngagementDashboardController;
use App\Domains\Engagement\Http\Controllers\ManagerEngagementDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/hcm')->middleware('auth')->group(function () {

    // 1. Employee Self-Service (ESS)
    Route::prefix('me/engagement')->group(function () {
        Route::get('/', [EmployeeEngagementDashboardController::class, 'dashboard']);
        Route::get('surveys', [EmployeeEngagementDashboardController::class, 'getSurveys']);
        Route::get('surveys/{campaign}', [EmployeeEngagementDashboardController::class, 'getSurveyDetails']);
        Route::post('surveys/{campaign}/start', [EmployeeEngagementDashboardController::class, 'startSurvey']);
        Route::post('surveys/{campaign}/submit', [EmployeeEngagementDashboardController::class, 'submitSurvey']);

        Route::get('recognition', [EmployeeEngagementDashboardController::class, 'getRecognition']);
        Route::post('recognition', [EmployeeEngagementDashboardController::class, 'postRecognition']);

        Route::get('suggestions', [EmployeeEngagementDashboardController::class, 'getSuggestions']);
        Route::post('suggestions', [EmployeeEngagementDashboardController::class, 'postSuggestion']);
        Route::post('suggestions/{suggestion}/vote', [EmployeeEngagementDashboardController::class, 'voteSuggestion']);

        Route::get('action-plans', [EmployeeEngagementDashboardController::class, 'getActionPlans']);
    });

    // 2. Manager Self-Service (MSS)
    Route::prefix('manager/engagement')->group(function () {
        Route::get('/', [ManagerEngagementDashboardController::class, 'dashboard']);
        Route::get('results/{campaign}', [ManagerEngagementDashboardController::class, 'getResults']);
        Route::get('action-plans', [ManagerEngagementDashboardController::class, 'getActionPlans']);
    });

    // 3. HR Admin — Surveys, Campaigns, Results & Culture
    Route::prefix('engagement')->group(function () {
        // Surveys & Builder
        Route::get('surveys', [AdminEngagementSurveyController::class, 'index']);
        Route::post('surveys', [AdminEngagementSurveyController::class, 'store']);
        Route::get('surveys/question-bank', [AdminEngagementSurveyController::class, 'getQuestionBank']);
        Route::get('surveys/templates', [AdminEngagementSurveyController::class, 'getTemplates']);
        Route::get('surveys/{survey}', [AdminEngagementSurveyController::class, 'show']);
        Route::patch('surveys/{survey}', [AdminEngagementSurveyController::class, 'update']);
        Route::post('surveys/{survey}/publish', [AdminEngagementSurveyController::class, 'publish']);
        Route::post('surveys/{survey}/clone', [AdminEngagementSurveyController::class, 'clone']);
        Route::post('surveys/{survey}/questions', [AdminEngagementSurveyController::class, 'addQuestion']);
        Route::post('surveys/{survey}/sections', [AdminEngagementSurveyController::class, 'addSection']);

        // Campaigns
        Route::get('campaigns', [AdminEngagementCampaignController::class, 'index']);
        Route::post('campaigns', [AdminEngagementCampaignController::class, 'store']);
        Route::get('campaigns/{campaign}', [AdminEngagementCampaignController::class, 'show']);
        Route::post('campaigns/{campaign}/launch', [AdminEngagementCampaignController::class, 'launch']);
        Route::post('campaigns/{campaign}/close', [AdminEngagementCampaignController::class, 'close']);
        Route::post('campaigns/{campaign}/remind', [AdminEngagementCampaignController::class, 'sendReminders']);

        // Results & Analytics
        Route::get('campaigns/{campaign}/results', [AdminEngagementResultController::class, 'getResults']);
        Route::get('campaigns/{campaign}/nps', [AdminEngagementResultController::class, 'getNps']);
        Route::get('campaigns/{campaign}/dimensions', [AdminEngagementResultController::class, 'getDimensionScores']);
        Route::get('campaigns/{campaign}/trends', [AdminEngagementResultController::class, 'getTrends']);
        Route::get('campaigns/{campaign}/text-analysis', [AdminEngagementResultController::class, 'getTextAnalysis']);

        // Action Plans
        Route::get('action-plans', [AdminActionPlanController::class, 'index']);
        Route::post('action-plans', [AdminActionPlanController::class, 'store']);
        Route::get('action-plans/{plan}', [AdminActionPlanController::class, 'show']);
        Route::patch('action-plans/{plan}', [AdminActionPlanController::class, 'update']);
        Route::post('action-plans/{plan}/items', [AdminActionPlanController::class, 'addItem']);
        Route::patch('action-items/{item}', [AdminActionPlanController::class, 'updateItem']);

        // Recognition
        Route::get('recognition', [AdminRecognitionController::class, 'index']);
        Route::post('recognition', [AdminRecognitionController::class, 'store']);
        Route::patch('recognition/{recognition}/moderate', [AdminRecognitionController::class, 'moderate']);

        // Suggestions
        Route::get('suggestions', [AdminSuggestionController::class, 'index']);
        Route::patch('suggestions/{suggestion}/status', [AdminSuggestionController::class, 'updateStatus']);

        // Culture & Goals
        Route::get('culture', [AdminCultureController::class, 'index']);
        Route::post('culture/initiatives', [AdminCultureController::class, 'storeInitiative']);
        Route::post('culture/goals', [AdminCultureController::class, 'storeGoal']);
        Route::patch('culture/goals/{goal}/progress', [AdminCultureController::class, 'updateGoalProgress']);

        // Reports
        Route::get('reports/overview', [AdminEngagementReportController::class, 'engagementOverview']);
        Route::get('reports/participation', [AdminEngagementReportController::class, 'participationReport']);
        Route::get('reports/recognition', [AdminEngagementReportController::class, 'recognitionReport']);
    });
});
