<?php

use App\Domains\OrganizationDesign\Http\Controllers\JobArchitectureController;
use App\Domains\OrganizationDesign\Http\Controllers\JobEvaluationController;
use App\Domains\OrganizationDesign\Http\Controllers\JobProfileController;
use App\Domains\OrganizationDesign\Http\Controllers\OrgDesignGovernanceController;
use App\Domains\OrganizationDesign\Http\Controllers\OrgDesignScenarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/org-design')->middleware(['api'])->group(function () {
    // 1. Job Architecture
    Route::get('job-architecture/tree', [JobArchitectureController::class, 'tree']);
    Route::post('job-architecture/families', [JobArchitectureController::class, 'storeFamily']);
    Route::post('job-architecture/families/{familyId}/sub-families', [JobArchitectureController::class, 'storeSubFamily']);
    Route::post('job-architecture/career-tracks', [JobArchitectureController::class, 'storeCareerTrack']);
    Route::post('job-architecture/job-levels', [JobArchitectureController::class, 'storeJobLevel']);

    // 2. Job Profiles & Impact
    Route::get('job-profiles', [JobProfileController::class, 'index']);
    Route::post('job-profiles', [JobProfileController::class, 'store']);
    Route::get('job-profiles/{id}', [JobProfileController::class, 'show']);
    Route::put('job-profiles/{id}', [JobProfileController::class, 'update']);
    Route::get('job-profiles/{id}/impact', [JobProfileController::class, 'impact']);

    // 3. Job Evaluation
    Route::post('job-profiles/{profileId}/evaluations', [JobEvaluationController::class, 'evaluate']);

    // 4. Organization Scenarios
    Route::get('scenarios', [OrgDesignScenarioController::class, 'index']);
    Route::post('scenarios', [OrgDesignScenarioController::class, 'store']);
    Route::post('scenarios/{scenarioId}/clone-live', [OrgDesignScenarioController::class, 'cloneLive']);
    Route::post('scenarios/{scenarioId}/nodes', [OrgDesignScenarioController::class, 'addNode']);
    Route::get('scenarios/{scenarioId}/compare', [OrgDesignScenarioController::class, 'compare']);

    // 5. Governance, Health & AI
    Route::get('governance/health-scan', [OrgDesignGovernanceController::class, 'healthScan']);
    Route::post('governance/ai/draft-profile', [OrgDesignGovernanceController::class, 'aiDraft']);
    Route::post('governance/ai/standardize-title', [OrgDesignGovernanceController::class, 'aiStandardizeTitle']);
});
