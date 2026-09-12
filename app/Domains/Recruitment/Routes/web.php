<?php

use App\Domains\Recruitment\Http\Controllers\PublicCareersController;
use App\Domains\Recruitment\Http\Controllers\RecruitmentDashboardController;
use Illuminate\Support\Facades\Route;

// Public Careers Web Pages (No Auth Required)
Route::prefix('careers')->group(function (): void {
    Route::get('/', [PublicCareersController::class, 'careersWeb'])->name('careers.index');
    Route::get('{slug}', [PublicCareersController::class, 'jobDetailWeb'])->name('careers.show');
});

// Authenticated Recruiter & ATS Dashboard
Route::middleware(['web', 'auth'])->prefix('recruitment')->group(function (): void {
    Route::get('/', [RecruitmentDashboardController::class, 'index'])->name('recruitment.dashboard');
});
