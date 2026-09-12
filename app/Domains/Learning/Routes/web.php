<?php

use App\Domains\Learning\Http\Controllers\LearningUIController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('hcm')->group(function (): void {
    // Employee Self-Service Web UI
    Route::prefix('me/learning')->group(function (): void {
        Route::get('/', [LearningUIController::class, 'employeeDashboard'])->name('hcm.me.learning.dashboard');
        Route::get('catalog', [LearningUIController::class, 'employeeCatalog'])->name('hcm.me.learning.catalog');
        Route::get('courses/{course}', [LearningUIController::class, 'employeeCourseDetail'])->name('hcm.me.learning.course_detail');
        Route::get('player/{item}', [LearningUIController::class, 'employeePlayer'])->name('hcm.me.learning.player');
        Route::get('assessments/{assessment}', [LearningUIController::class, 'employeeAssessment'])->name('hcm.me.learning.assessment');
        Route::get('transcript', [LearningUIController::class, 'employeeTranscript'])->name('hcm.me.learning.transcript');
    });

    // Manager Self-Service Web UI
    Route::prefix('manager/learning')->group(function (): void {
        Route::get('/', [LearningUIController::class, 'managerDashboard'])->name('hcm.manager.learning.dashboard');
        Route::get('nominations', [LearningUIController::class, 'managerNominations'])->name('hcm.manager.learning.nominations');
    });

    // Admin & Learning Management Web UI
    Route::prefix('learning')->group(function (): void {
        Route::get('/', [LearningUIController::class, 'adminDashboard'])->name('hcm.learning.dashboard');
        Route::get('courses', [LearningUIController::class, 'adminCourses'])->name('hcm.learning.courses');
        Route::get('courses/{course}/builder', [LearningUIController::class, 'adminCourseBuilder'])->name('hcm.learning.course_builder');
        Route::get('sessions', [LearningUIController::class, 'adminSessions'])->name('hcm.learning.sessions');
        Route::get('enrollments', [LearningUIController::class, 'adminEnrollments'])->name('hcm.learning.enrollments');
        Route::get('requirements', [LearningUIController::class, 'adminRequirements'])->name('hcm.learning.requirements');
        Route::get('compliance', [LearningUIController::class, 'adminCompliance'])->name('hcm.learning.compliance');
        Route::get('reports', [LearningUIController::class, 'adminReports'])->name('hcm.learning.reports');
    });
});

// Public Certificate Verification Web View (Unauthenticated)
Route::middleware('web')->get('verify/certificate/{code}', [\App\Domains\Learning\Http\Controllers\CertificateVerificationController::class, 'verifyWeb'])->name('learning.certificate.verify');
