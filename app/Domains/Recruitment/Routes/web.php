<?php

use App\Domains\Recruitment\Http\Controllers\PublicCareersController;
use App\Domains\Recruitment\Http\Controllers\RecruitmentDashboardController;
use Illuminate\Support\Facades\Route;

// Public Careers Web Pages (No Auth Required)
Route::prefix('careers')->group(function (): void {
    Route::get('/', [PublicCareersController::class, 'careersWeb'])->name('careers.index');
    Route::get('{slug}', [PublicCareersController::class, 'jobDetailWeb'])->name('careers.show');
});

// Authenticated Recruiter & ATS Dashboard with Public Marketing Fallback
Route::prefix('recruitment')->middleware(['web'])->group(function (): void {
    Route::get('/', function (\Illuminate\Http\Request $request) {
        if (auth()->check()) {
            return app(\App\Domains\Recruitment\Http\Controllers\RecruitmentDashboardController::class)->index($request);
        }
        return app(\App\Domains\PublicWebsite\Http\Controllers\PublicWebsiteController::class)->feature($request, 'recruitment');
    })->name('recruitment.public');

    Route::middleware(['auth'])->group(function (): void {
        Route::get('/dashboard', [RecruitmentDashboardController::class, 'index'])->name('recruitment.dashboard');
    });
});
