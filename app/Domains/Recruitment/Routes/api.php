<?php

use App\Domains\Recruitment\Http\Controllers\ApplicationController;
use App\Domains\Recruitment\Http\Controllers\CandidateController;
use App\Domains\Recruitment\Http\Controllers\InterviewController;
use App\Domains\Recruitment\Http\Controllers\OfferController;
use App\Domains\Recruitment\Http\Controllers\PublicCareersController;
use App\Domains\Recruitment\Http\Controllers\RecruitmentAiController;
use App\Domains\Recruitment\Http\Controllers\RecruitmentAnalyticsController;
use App\Domains\Recruitment\Http\Controllers\RequisitionController;
use Illuminate\Support\Facades\Route;

// 1. Public Candidate & Careers Endpoints (No Auth Required)
Route::prefix('v1/public/recruitment')->group(function (): void {
    Route::post('postings/{posting}/apply', [PublicCareersController::class, 'applyPublic']);
});

// 2. Authenticated ATS & Recruiter Management Endpoints
Route::middleware('auth')->prefix('v1/hcm/recruitment')->group(function (): void {
    // Requisitions
    Route::get('requisitions', [RequisitionController::class, 'index']);
    Route::post('requisitions', [RequisitionController::class, 'store']);
    Route::get('requisitions/{id}', [RequisitionController::class, 'show']);
    Route::post('requisitions/{id}/submit', [RequisitionController::class, 'submit']);
    Route::post('requisitions/{id}/approve', [RequisitionController::class, 'approve']);

    // Candidates
    Route::get('candidates', [CandidateController::class, 'index']);
    Route::post('candidates', [CandidateController::class, 'store']);
    Route::get('candidates/{id}', [CandidateController::class, 'show']);
    Route::post('candidates/check-duplicates', [CandidateController::class, 'checkDuplicates']);

    // Applications
    Route::get('applications', [ApplicationController::class, 'index']);
    Route::post('applications', [ApplicationController::class, 'store']);
    Route::get('applications/{id}', [ApplicationController::class, 'show']);
    Route::post('applications/{id}/stage', [ApplicationController::class, 'changeStage']);
    Route::post('applications/{id}/screen', [ApplicationController::class, 'screen']);
    Route::post('applications/{id}/hire', [ApplicationController::class, 'hire']);

    // Interviews
    Route::post('interviews', [InterviewController::class, 'store']);
    Route::post('interviews/{id}/evaluate', [InterviewController::class, 'evaluate']);
    Route::get('interviews/{id}/summary', [InterviewController::class, 'summary']);

    // Offers
    Route::post('offers', [OfferController::class, 'store']);
    Route::get('offers/{id}', [OfferController::class, 'show']);
    Route::post('offers/{id}/version', [OfferController::class, 'updateVersion']);
    Route::post('offers/{id}/approve', [OfferController::class, 'approve']);
    Route::post('offers/{id}/send', [OfferController::class, 'send']);
    Route::post('offers/{id}/accept', [OfferController::class, 'accept']);

    // Analytics
    Route::get('analytics/funnel', [RecruitmentAnalyticsController::class, 'funnel']);
    Route::get('analytics/kpis', [RecruitmentAnalyticsController::class, 'kpis']);

    // AI Assistance & Guardrails
    Route::post('ai/match', [RecruitmentAiController::class, 'match']);
    Route::post('ai/job-description', [RecruitmentAiController::class, 'generateJobDescription']);
    Route::post('ai/query', [RecruitmentAiController::class, 'query']);
});
