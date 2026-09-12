<?php

use App\Domains\Engagement\Http\Controllers\EngagementUIController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('hcm')->group(function () {

    // Employee Self-Service
    Route::prefix('me/engagement')->group(function () {
        Route::get('/', [EngagementUIController::class, 'employeeDashboard'])->name('engagement.employee.dashboard');
        Route::get('surveys/{campaign}', [EngagementUIController::class, 'employeeTakeSurvey'])->name('engagement.employee.take');
        Route::get('pulse', [EngagementUIController::class, 'employeePulse'])->name('engagement.employee.pulse');
        Route::get('recognition', [EngagementUIController::class, 'employeeRecognition'])->name('engagement.employee.recognition');
        Route::get('suggestions', [EngagementUIController::class, 'employeeSuggestions'])->name('engagement.employee.suggestions');
    });

    // Manager Self-Service
    Route::prefix('manager/engagement')->group(function () {
        Route::get('/', [EngagementUIController::class, 'managerDashboard'])->name('engagement.manager.dashboard');
        Route::get('results/{campaign}', [EngagementUIController::class, 'managerTeamResults'])->name('engagement.manager.results');
    });

    // HR & Admin Portal
    Route::prefix('engagement')->group(function () {
        Route::get('/', [EngagementUIController::class, 'adminDashboard'])->name('engagement.admin.dashboard');
        Route::get('surveys', [EngagementUIController::class, 'adminSurveys'])->name('engagement.admin.surveys');
        Route::get('surveys/{survey}/builder', [EngagementUIController::class, 'adminSurveyBuilder'])->name('engagement.admin.builder');
        Route::get('campaigns/{campaign}/results', [EngagementUIController::class, 'adminCampaignResults'])->name('engagement.admin.results');
        Route::get('action-plans', [EngagementUIController::class, 'adminActionPlans'])->name('engagement.admin.action_plans');
        Route::get('culture', [EngagementUIController::class, 'adminCulture'])->name('engagement.admin.culture');
        Route::get('executive', [EngagementUIController::class, 'adminExecutive'])->name('engagement.admin.executive');
    });
});
