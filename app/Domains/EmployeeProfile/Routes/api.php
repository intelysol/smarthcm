<?php

use App\Domains\EmployeeProfile\Http\Controllers\EmployeeDirectoryController;
use App\Domains\EmployeeProfile\Http\Controllers\EmployeeProfileAiController;
use App\Domains\EmployeeProfile\Http\Controllers\EmployeeProfileController;
use App\Domains\EmployeeProfile\Http\Controllers\OrgChartController;
use App\Domains\EmployeeProfile\Http\Controllers\PeopleSearchController;
use App\Domains\EmployeeProfile\Http\Controllers\ProfileChangeRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/hcm')->middleware(['api'])->group(function () {

    // 1. Directory & Search
    Route::get('employees', [EmployeeDirectoryController::class, 'index']);
    Route::get('people-search', [PeopleSearchController::class, 'search']);
    Route::get('people-search/autocomplete', [PeopleSearchController::class, 'autocomplete']);

    // 2. Org Chart
    Route::get('org-chart', [OrgChartController::class, 'index']);
    Route::get('org-chart/{managerId}/children', [OrgChartController::class, 'children']);
    Route::get('org-chart/{employeeId}/focused', [OrgChartController::class, 'focusedSubtree']);

    // 3. Profile & Summaries
    Route::get('employees/{id}', [EmployeeProfileController::class, 'show']);
    Route::get('employees/{id}/profile-summary', [EmployeeProfileController::class, 'summary']);
    Route::get('employees/{id}/timeline', [EmployeeProfileController::class, 'timeline']);
    Route::put('employees/{id}/preferences', [EmployeeProfileController::class, 'updatePreferences']);

    // 4. Profile Change Requests
    Route::get('profile-change-requests', [ProfileChangeRequestController::class, 'index']);
    Route::post('employees/{id}/profile-change-requests', [ProfileChangeRequestController::class, 'store']);
    Route::post('profile-change-requests/{id}/approve', [ProfileChangeRequestController::class, 'approve']);
    Route::post('profile-change-requests/{id}/reject', [ProfileChangeRequestController::class, 'reject']);

    // 5. AI Assistant & Advisory
    Route::post('profile/ai/nl-search', [EmployeeProfileAiController::class, 'nlSearch']);
    Route::get('employees/{id}/ai-summary', [EmployeeProfileAiController::class, 'generateSummary']);
    Route::post('profile/ai/inquire', [EmployeeProfileAiController::class, 'inquire']);
});
