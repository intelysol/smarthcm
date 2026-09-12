<?php

use App\Domains\Learning\Http\Controllers\AdminCertificateController;
use App\Domains\Learning\Http\Controllers\AdminCourseController;
use App\Domains\Learning\Http\Controllers\AdminEnrollmentController;
use App\Domains\Learning\Http\Controllers\AdminPathController;
use App\Domains\Learning\Http\Controllers\AdminProgramController;
use App\Domains\Learning\Http\Controllers\AdminProviderInstructorVenueController;
use App\Domains\Learning\Http\Controllers\AdminReportController;
use App\Domains\Learning\Http\Controllers\AdminRequirementController;
use App\Domains\Learning\Http\Controllers\AdminSessionController;
use App\Domains\Learning\Http\Controllers\EmployeeAssessmentController;
use App\Domains\Learning\Http\Controllers\EmployeeCourseCatalogController;
use App\Domains\Learning\Http\Controllers\EmployeeLearningDashboardController;
use App\Domains\Learning\Http\Controllers\EmployeeLearningProgressController;
use App\Domains\Learning\Http\Controllers\ManagerLearningController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('v1/hcm')->group(function (): void {
    // Employee Self-Service (ESS)
    Route::prefix('me/learning')->group(function (): void {
        Route::get('/', [EmployeeLearningDashboardController::class, 'dashboard']);
        Route::get('catalog', [EmployeeCourseCatalogController::class, 'catalog']);
        Route::get('courses/{course}', [EmployeeCourseCatalogController::class, 'show']);
        Route::post('courses/{course}/enroll', [EmployeeCourseCatalogController::class, 'enroll']);
        Route::post('enrollments/{enrollment}/withdraw', [EmployeeCourseCatalogController::class, 'withdraw']);

        Route::get('enrollments', [EmployeeLearningProgressController::class, 'enrollments']);
        Route::get('progress', [EmployeeLearningProgressController::class, 'progress']);
        Route::post('items/{item}/progress', [EmployeeLearningProgressController::class, 'updateItemProgress']);

        Route::get('assessments/{assessment}', [EmployeeAssessmentController::class, 'show']);
        Route::post('assessments/{assessment}/attempts', [EmployeeAssessmentController::class, 'startAttempt']);
        Route::post('attempts/{attempt}/submit', [EmployeeAssessmentController::class, 'submitAttempt']);

        Route::get('certificates', [EmployeeLearningDashboardController::class, 'certificates']);
        Route::get('transcript', [EmployeeLearningDashboardController::class, 'transcript']);
        Route::get('requirements', [EmployeeLearningDashboardController::class, 'requirements']);
    });

    // Manager Self-Service (MSS)
    Route::prefix('manager/learning')->group(function (): void {
        Route::get('/', [ManagerLearningController::class, 'dashboard']);
        Route::get('team', [ManagerLearningController::class, 'team']);
        Route::get('nominations', [ManagerLearningController::class, 'nominations']);
        Route::post('nominations', [ManagerLearningController::class, 'storeNomination']);
    });

    // Admin & Learning Management
    Route::prefix('learning')->group(function (): void {
        // Courses
        Route::get('courses', [AdminCourseController::class, 'index']);
        Route::post('courses', [AdminCourseController::class, 'store']);
        Route::get('courses/{course}', [AdminCourseController::class, 'show']);
        Route::patch('courses/{course}', [AdminCourseController::class, 'update']);
        Route::post('courses/{course}/publish', [AdminCourseController::class, 'publish']);
        Route::post('courses/{course}/version', [AdminCourseController::class, 'version']);
        Route::post('courses/{course}/modules', [AdminCourseController::class, 'addModule']);
        Route::post('courses/{course}/objectives', [AdminCourseController::class, 'addObjective']);
        Route::post('courses/{course}/prerequisites', [AdminCourseController::class, 'addPrerequisite']);

        // Programs
        Route::get('programs', [AdminProgramController::class, 'index']);
        Route::post('programs', [AdminProgramController::class, 'store']);
        Route::post('programs/{program}/courses', [AdminProgramController::class, 'addCourse']);

        // Paths
        Route::get('paths', [AdminPathController::class, 'index']);
        Route::post('paths', [AdminPathController::class, 'store']);
        Route::post('paths/{path}/items', [AdminPathController::class, 'addItem']);

        // Sessions & Attendance
        Route::get('sessions', [AdminSessionController::class, 'index']);
        Route::post('sessions', [AdminSessionController::class, 'store']);
        Route::patch('sessions/{session}', [AdminSessionController::class, 'update']);
        Route::post('sessions/{session}/attendance', [AdminSessionController::class, 'recordAttendance']);

        // Enrollments
        Route::get('enrollments', [AdminEnrollmentController::class, 'index']);
        Route::post('enrollments', [AdminEnrollmentController::class, 'store']);
        Route::post('enrollments/{enrollment}/approve', [AdminEnrollmentController::class, 'approve']);
        Route::post('enrollments/{enrollment}/cancel', [AdminEnrollmentController::class, 'cancel']);

        // Requirements
        Route::get('requirements', [AdminRequirementController::class, 'index']);
        Route::post('requirements', [AdminRequirementController::class, 'store']);
        Route::get('requirements/{requirement}/assignments', [AdminRequirementController::class, 'assignments']);
        Route::post('requirement-assignments/{assignment}/waive', [AdminRequirementController::class, 'waive']);

        // Certificates
        Route::get('certificates', [AdminCertificateController::class, 'index']);
        Route::post('certificates/{certificate}/verify', [AdminCertificateController::class, 'verify']);
        Route::post('certificates/{certificate}/revoke', [AdminCertificateController::class, 'revoke']);

        // Providers, Instructors, Venues
        Route::get('providers', [AdminProviderInstructorVenueController::class, 'providers']);
        Route::post('providers', [AdminProviderInstructorVenueController::class, 'storeProvider']);
        Route::get('instructors', [AdminProviderInstructorVenueController::class, 'instructors']);
        Route::post('instructors', [AdminProviderInstructorVenueController::class, 'storeInstructor']);
        Route::get('venues', [AdminProviderInstructorVenueController::class, 'venues']);
        Route::post('venues', [AdminProviderInstructorVenueController::class, 'storeVenue']);

        // Reports
        Route::get('reports/completion', [AdminReportController::class, 'completion']);
        Route::get('reports/compliance', [AdminReportController::class, 'compliance']);
        Route::get('reports/certifications', [AdminReportController::class, 'certifications']);
        Route::get('reports/cost', [AdminReportController::class, 'cost']);

        // Individual Development Plans (IDP)
        Route::get('development-plans', [\App\Domains\Learning\Http\Controllers\EmployeeDevelopmentPlanController::class, 'index']);
        Route::post('development-plans', [\App\Domains\Learning\Http\Controllers\EmployeeDevelopmentPlanController::class, 'store']);
        Route::post('development-plans/{plan}/activities', [\App\Domains\Learning\Http\Controllers\EmployeeDevelopmentPlanController::class, 'addActivity']);
        Route::post('development-activities/{activity}/complete', [\App\Domains\Learning\Http\Controllers\EmployeeDevelopmentPlanController::class, 'completeActivity']);

        // AI Recommendations & Guidance
        Route::get('ai/recommendations', [\App\Domains\Learning\Http\Controllers\LearningAiController::class, 'recommendations']);
        Route::post('ai/query', [\App\Domains\Learning\Http\Controllers\LearningAiController::class, 'query']);
    });
});

// Public Certificate Verification Endpoint (Unauthenticated)
Route::get('v1/hcm/verify/certificate/{code}', [\App\Domains\Learning\Http\Controllers\CertificateVerificationController::class, 'verifyApi']);
