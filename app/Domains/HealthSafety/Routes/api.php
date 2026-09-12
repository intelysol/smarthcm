<?php

declare(strict_types=1);

use App\Domains\HealthSafety\Http\Controllers\CorrectiveActionController;
use App\Domains\HealthSafety\Http\Controllers\HealthAiController;
use App\Domains\HealthSafety\Http\Controllers\HealthDashboardController;
use App\Domains\HealthSafety\Http\Controllers\HealthRequirementController;
use App\Domains\HealthSafety\Http\Controllers\MedicalAssessmentController;
use App\Domains\HealthSafety\Http\Controllers\MedicalFitnessController;
use App\Domains\HealthSafety\Http\Controllers\MedicalRestrictionController;
use App\Domains\HealthSafety\Http\Controllers\ReturnToWorkController;
use App\Domains\HealthSafety\Http\Controllers\SafetyIncidentController;
use App\Domains\HealthSafety\Http\Controllers\SafetyInvestigationController;
use App\Domains\HealthSafety\Http\Controllers\WorkplaceAccommodationController;
use App\Domains\HealthSafety\Http\Controllers\WorkplaceExposureController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('v1/hcm/health')->group(function () {
    // Requirements
    Route::get('requirements/types', [HealthRequirementController::class, 'types']);
    Route::get('requirements', [HealthRequirementController::class, 'index']);
    Route::post('requirements', [HealthRequirementController::class, 'store']);
    Route::get('requirements/employee/{employeeId}', [HealthRequirementController::class, 'employeeRequirements']);
    Route::post('requirements/employee/{employeeId}/evaluate', [HealthRequirementController::class, 'evaluateApplicability']);

    // Assessments
    Route::post('assessments/schedule', [MedicalAssessmentController::class, 'schedule']);
    Route::post('assessments/{id}/complete', [MedicalAssessmentController::class, 'complete']);
    Route::get('assessments/{id}', [MedicalAssessmentController::class, 'show']);

    // Fitness
    Route::post('fitness', [MedicalFitnessController::class, 'record']);
    Route::get('fitness/employee/{employeeId}', [MedicalFitnessController::class, 'employeeFitness']);

    // Restrictions
    Route::post('restrictions', [MedicalRestrictionController::class, 'store']);
    Route::get('restrictions/employee/{employeeId}', [MedicalRestrictionController::class, 'employeeRestrictions']);
    Route::post('restrictions/{id}/lift', [MedicalRestrictionController::class, 'lift']);

    // Return to Work
    Route::post('return-to-work', [ReturnToWorkController::class, 'initiate']);
    Route::post('return-to-work/{id}/plan', [ReturnToWorkController::class, 'createPlan']);
    Route::post('return-to-work/{id}/complete', [ReturnToWorkController::class, 'complete']);
    Route::get('return-to-work/employee/{employeeId}', [ReturnToWorkController::class, 'employeeCases']);

    // Accommodations
    Route::post('accommodations', [WorkplaceAccommodationController::class, 'store']);
    Route::post('accommodations/{id}/assess', [WorkplaceAccommodationController::class, 'assess']);
    Route::post('accommodations/{id}/implement', [WorkplaceAccommodationController::class, 'implement']);

    // Incidents
    Route::get('incidents', [SafetyIncidentController::class, 'index']);
    Route::post('incidents', [SafetyIncidentController::class, 'store']);
    Route::get('incidents/{id}', [SafetyIncidentController::class, 'show']);
    Route::post('incidents/{id}/escalate', [SafetyIncidentController::class, 'escalate']);
    Route::post('incidents/{id}/close', [SafetyIncidentController::class, 'close']);

    // Investigations
    Route::post('investigations', [SafetyInvestigationController::class, 'start']);
    Route::post('investigations/{id}/findings', [SafetyInvestigationController::class, 'updateFindings']);
    Route::post('investigations/{id}/complete', [SafetyInvestigationController::class, 'complete']);

    // Actions (CAPA)
    Route::post('actions', [CorrectiveActionController::class, 'store']);
    Route::post('actions/{id}/complete', [CorrectiveActionController::class, 'complete']);
    Route::post('actions/{id}/verify', [CorrectiveActionController::class, 'verify']);

    // Exposures
    Route::get('exposures', [WorkplaceExposureController::class, 'index']);
    Route::post('exposures', [WorkplaceExposureController::class, 'store']);
    Route::get('exposures/employee/{employeeId}', [WorkplaceExposureController::class, 'employeeExposures']);

    // Dashboard & Metrics
    Route::get('dashboard/metrics', [HealthDashboardController::class, 'metrics']);
    Route::get('reports/osha-300', [HealthDashboardController::class, 'oshaLog']);

    // AI Advisory
    Route::post('ai/analyze-incident', [HealthAiController::class, 'analyzeIncident']);
    Route::post('ai/advise-accommodations', [HealthAiController::class, 'adviseAccommodations']);
});
