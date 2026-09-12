<?php

use App\Domains\Onboarding\Http\Controllers\OnboardingAiController;
use App\Domains\Onboarding\Http\Controllers\OnboardingAnalyticsController;
use App\Domains\Onboarding\Http\Controllers\OnboardingCaseController;
use App\Domains\Onboarding\Http\Controllers\OnboardingDocumentController;
use App\Domains\Onboarding\Http\Controllers\OnboardingEmployeePortalController;
use App\Domains\Onboarding\Http\Controllers\OnboardingProbationController;
use App\Domains\Onboarding\Http\Controllers\OnboardingTaskController;
use Illuminate\Support\Facades\Route;

// 1. Employee Self-Service Onboarding APIs
Route::middleware('auth')->prefix('v1/me/onboarding')->group(function (): void {
    Route::get('/', [OnboardingEmployeePortalController::class, 'getMyCase']);
    Route::get('tasks', [OnboardingEmployeePortalController::class, 'getMyTasks']);
    Route::post('tasks/{id}/complete', [OnboardingEmployeePortalController::class, 'completeMyTask']);
    Route::post('policies/acknowledge', [OnboardingEmployeePortalController::class, 'acknowledgeMyPolicy']);
    Route::post('forms/{formId}', [OnboardingEmployeePortalController::class, 'submitMyForm']);
});

// 2. HR & Manager Enterprise Onboarding APIs
Route::middleware('auth')->prefix('v1/hcm/onboarding')->group(function (): void {
    // Cases
    Route::get('cases', [OnboardingCaseController::class, 'index']);
    Route::post('cases', [OnboardingCaseController::class, 'store']);
    Route::get('cases/{id}', [OnboardingCaseController::class, 'show']);

    // Tasks & Dependencies
    Route::post('tasks/{id}/complete', [OnboardingTaskController::class, 'complete']);
    Route::post('tasks/{id}/block', [OnboardingTaskController::class, 'block']);
    Route::post('tasks/{id}/prerequisite', [OnboardingTaskController::class, 'addPrerequisite']);

    // Documents
    Route::post('documents/{id}/submit', [OnboardingDocumentController::class, 'submit']);
    Route::post('documents/{id}/verify', [OnboardingDocumentController::class, 'verify']);
    Route::post('documents/{id}/reject', [OnboardingDocumentController::class, 'reject']);

    // Probation
    Route::get('probations', [OnboardingProbationController::class, 'index']);
    Route::post('probations/{id}/extend', [OnboardingProbationController::class, 'extend']);
    Route::post('probations/{id}/review', [OnboardingProbationController::class, 'review']);

    // Analytics
    Route::get('analytics/kpis', [OnboardingAnalyticsController::class, 'kpis']);

    // AI Assistance
    Route::get('cases/{id}/ai/welcome', [OnboardingAiController::class, 'welcome']);
    Route::get('cases/{id}/ai/readiness', [OnboardingAiController::class, 'readiness']);
    Route::post('ai/query', [OnboardingAiController::class, 'query']);
});
