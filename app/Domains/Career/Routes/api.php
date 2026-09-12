<?php

use App\Domains\Career\Http\Controllers\AdminCareerPathController;
use App\Domains\Career\Http\Controllers\AdminSkillMasterController;
use App\Domains\Career\Http\Controllers\AdminSuccessionController;
use App\Domains\Career\Http\Controllers\AdminTalentPoolController;
use App\Domains\Career\Http\Controllers\AdminTalentReportController;
use App\Domains\Career\Http\Controllers\AdminTalentReviewController;
use App\Domains\Career\Http\Controllers\EmployeeCareerDashboardController;
use App\Domains\Career\Http\Controllers\EmployeeSkillsDashboardController;
use App\Domains\Career\Http\Controllers\ManagerCareerDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/hcm')->middleware('auth')->group(function () {

    // 1. Employee Self-Service (ESS)
    Route::prefix('me')->group(function () {
        // Skills
        Route::get('skills', [EmployeeSkillsDashboardController::class, 'index']);
        Route::post('skills', [EmployeeSkillsDashboardController::class, 'store']);
        Route::post('skills/{employeeSkill}/evidence', [EmployeeSkillsDashboardController::class, 'attachEvidence']);

        // Career & Development
        Route::get('career', [EmployeeCareerDashboardController::class, 'dashboard']);
        Route::get('career/path', [EmployeeCareerDashboardController::class, 'careerPath']);
        Route::post('career/aspirations', [EmployeeCareerDashboardController::class, 'storeAspiration']);
        Route::get('career/plans', [EmployeeCareerDashboardController::class, 'plans']);
        Route::post('career/plans', [EmployeeCareerDashboardController::class, 'storePlan']);
        Route::post('career/plans/{plan}/actions', [EmployeeCareerDashboardController::class, 'storePlanAction']);
        Route::patch('career/actions/{action}/progress', [EmployeeCareerDashboardController::class, 'updateActionProgress']);
    });

    // 2. Manager Self-Service (MSS)
    Route::prefix('manager/career')->group(function () {
        Route::get('dashboard', [ManagerCareerDashboardController::class, 'dashboard']);
        Route::get('team-skills', [ManagerCareerDashboardController::class, 'teamSkills']);
        Route::post('skills/{employeeSkill}/verify', [ManagerCareerDashboardController::class, 'verifySkill']);
        Route::post('plans/{plan}/approve', [ManagerCareerDashboardController::class, 'approvePlan']);
    });

    // 3. Admin & HR Talent Management
    Route::prefix('career')->group(function () {
        // Skills Master
        Route::get('skills', [AdminSkillMasterController::class, 'skills']);
        Route::post('skills', [AdminSkillMasterController::class, 'storeSkill']);
        Route::get('categories', [AdminSkillMasterController::class, 'categories']);
        Route::post('categories', [AdminSkillMasterController::class, 'storeCategory']);
        Route::get('levels', [AdminSkillMasterController::class, 'levels']);
        Route::post('requirements', [AdminSkillMasterController::class, 'storeJobRequirement']);

        // Career Paths
        Route::get('paths', [AdminCareerPathController::class, 'index']);
        Route::post('paths', [AdminCareerPathController::class, 'store']);
        Route::post('paths/{path}/steps', [AdminCareerPathController::class, 'storeStep']);
    });

    Route::prefix('talent')->group(function () {
        // Talent Pools
        Route::get('pools', [AdminTalentPoolController::class, 'index']);
        Route::post('pools', [AdminTalentPoolController::class, 'store']);
        Route::get('pools/{pool}/members', [AdminTalentPoolController::class, 'members']);
        Route::post('pools/{pool}/members', [AdminTalentPoolController::class, 'addMember']);
        Route::delete('pools/{pool}/members/{employee}', [AdminTalentPoolController::class, 'removeMember']);

        // Talent Reviews & 9-Box
        Route::get('reviews', [AdminTalentReviewController::class, 'sessions']);
        Route::post('reviews', [AdminTalentReviewController::class, 'storeSession']);
        Route::get('reviews/{session}/records', [AdminTalentReviewController::class, 'records']);
        Route::post('reviews/{session}/placement', [AdminTalentReviewController::class, 'recordPlacement']);
        Route::post('reviews/records/{record}/override', [AdminTalentReviewController::class, 'overridePlacement']);
        Route::get('matrix/nine-box', [AdminTalentReviewController::class, 'nineBoxMatrix']);

        // Succession Management
        Route::get('succession/plans', [AdminSuccessionController::class, 'plans']);
        Route::post('succession/plans', [AdminSuccessionController::class, 'storePlan']);
        Route::get('succession/plans/{plan}/positions', [AdminSuccessionController::class, 'positions']);
        Route::post('succession/plans/{plan}/positions', [AdminSuccessionController::class, 'storePosition']);
        Route::get('succession/positions/{position}/candidates', [AdminSuccessionController::class, 'candidates']);
        Route::post('succession/positions/{position}/candidates', [AdminSuccessionController::class, 'storeCandidate']);
        Route::get('succession/plans/{plan}/scenarios', [AdminSuccessionController::class, 'scenarios']);
        Route::post('succession/plans/{plan}/scenarios', [AdminSuccessionController::class, 'storeScenario']);
        Route::post('succession/scenarios/{scenario}/candidates', [AdminSuccessionController::class, 'storeScenarioCandidate']);
        Route::get('succession/risk-summary', [AdminSuccessionController::class, 'riskSummary']);

        // Reports & Analytics
        Route::get('reports/skills', [AdminTalentReportController::class, 'skillInventory']);
        Route::get('reports/gaps', [AdminTalentReportController::class, 'skillGaps']);
        Route::get('reports/readiness', [AdminTalentReportController::class, 'careerReadiness']);
        Route::get('reports/pools', [AdminTalentReportController::class, 'talentPools']);
        Route::get('reports/succession', [AdminTalentReportController::class, 'successionCoverage']);
        Route::get('reports/development', [AdminTalentReportController::class, 'development']);
        Route::get('reports/analytics-snapshot', [AdminTalentReportController::class, 'analyticsSnapshot']);
    });
});
