<?php

use App\Domains\Career\Http\Controllers\CareerUIController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('hcm')->group(function () {

    // ESS Routes
    Route::get('me/skills', [CareerUIController::class, 'employeeSkills'])->name('career.employee.skills');
    Route::get('me/career', [CareerUIController::class, 'employeeCareerDashboard'])->name('career.employee.dashboard');
    Route::get('me/career/path', [CareerUIController::class, 'employeeCareerPath'])->name('career.employee.path');
    Route::get('me/career/plans', [CareerUIController::class, 'employeeCareerPlans'])->name('career.employee.plans');
    Route::get('me/career/mentoring', [CareerUIController::class, 'employeeMentoring'])->name('career.employee.mentoring');

    // MSS Routes
    Route::get('manager/career', [CareerUIController::class, 'managerDashboard'])->name('career.manager.dashboard');
    Route::get('manager/career/skills', [CareerUIController::class, 'managerTeamSkills'])->name('career.manager.team_skills');

    // Talent Admin & HR Routes
    Route::get('talent', [CareerUIController::class, 'adminTalentDashboard'])->name('career.admin.dashboard');
    Route::get('talent/skills', [CareerUIController::class, 'adminSkillMatrix'])->name('career.admin.skills');
    Route::get('talent/nine-box', [CareerUIController::class, 'adminNineBox'])->name('career.admin.nine_box');
    Route::get('talent/succession', [CareerUIController::class, 'adminSuccession'])->name('career.admin.succession');
    Route::get('talent/succession/positions/{position}', [CareerUIController::class, 'adminCriticalPosition'])->name('career.admin.position_detail');
    Route::get('talent/pools', [CareerUIController::class, 'adminTalentPools'])->name('career.admin.pools');
    Route::get('talent/reports', [CareerUIController::class, 'adminReports'])->name('career.admin.reports');
});
