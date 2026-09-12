<?php

use App\Domains\Mobility\Http\Controllers\MobilityAssignmentController;
use App\Domains\Mobility\Http\Controllers\MobilityBusinessTravelerController;
use App\Domains\Mobility\Http\Controllers\MobilityLifecycleController;
use App\Domains\Mobility\Http\Controllers\MobilityProgramController;
use App\Domains\Mobility\Http\Controllers\MobilityRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/hcm/mobility')->middleware(['api', 'auth'])->group(function () {
    // 1. Mobility Programs & Policies
    Route::get('/programs', [MobilityProgramController::class, 'index']);
    Route::post('/programs', [MobilityProgramController::class, 'store']);
    Route::get('/programs/{program}', [MobilityProgramController::class, 'show']);

    // 2. Mobility Requests & Approvals
    Route::get('/requests', [MobilityRequestController::class, 'index']);
    Route::post('/requests', [MobilityRequestController::class, 'store']);
    Route::get('/requests/{mobilityRequest}', [MobilityRequestController::class, 'show']);
    Route::post('/requests/{mobilityRequest}/submit', [MobilityRequestController::class, 'submit']);
    Route::post('/requests/{mobilityRequest}/approve', [MobilityRequestController::class, 'approve']);
    Route::post('/requests/{mobilityRequest}/reject', [MobilityRequestController::class, 'reject']);

    // 3. Mobility Assignments, Costs & Allocations
    Route::get('/assignments', [MobilityAssignmentController::class, 'index']);
    Route::post('/requests/{mobilityRequest}/create-assignment', [MobilityAssignmentController::class, 'storeFromRequest']);
    Route::get('/assignments/{assignment}', [MobilityAssignmentController::class, 'show']);
    Route::post('/assignments/{assignment}/activate', [MobilityAssignmentController::class, 'activate']);
    Route::post('/assignments/{assignment}/complete', [MobilityAssignmentController::class, 'complete']);
    Route::post('/assignments/{assignment}/costs', [MobilityAssignmentController::class, 'addCost']);
    Route::post('/assignments/{assignment}/allocate-costs', [MobilityAssignmentController::class, 'allocateCosts']);

    // 4. Relocation, Lifecycle, Tasks, Extensions, Changes & Repatriation
    Route::post('/assignments/{assignment}/relocation', [MobilityLifecycleController::class, 'initiateRelocation']);
    Route::post('/relocation-items/{item}/complete', [MobilityLifecycleController::class, 'completeRelocationItem']);
    Route::post('/assignments/{assignment}/generate-tasks', [MobilityLifecycleController::class, 'generateTasks']);
    Route::post('/tasks/{task}/complete', [MobilityLifecycleController::class, 'completeTask']);
    Route::post('/assignments/{assignment}/extensions', [MobilityLifecycleController::class, 'requestExtension']);
    Route::post('/extensions/{extension}/approve', [MobilityLifecycleController::class, 'approveExtension']);
    Route::post('/assignments/{assignment}/changes', [MobilityLifecycleController::class, 'requestChange']);
    Route::post('/changes/{change}/approve', [MobilityLifecycleController::class, 'approveChange']);
    Route::post('/assignments/{assignment}/repatriation', [MobilityLifecycleController::class, 'initiateRepatriation']);
    Route::post('/repatriations/{repatriation}/complete', [MobilityLifecycleController::class, 'completeRepatriation']);
    Route::get('/assignments/{assignment}/ai-briefing', [MobilityLifecycleController::class, 'aiBriefing']);

    // 5. Business Travelers
    Route::get('/business-travelers', [MobilityBusinessTravelerController::class, 'index']);
    Route::post('/business-travelers', [MobilityBusinessTravelerController::class, 'store']);
    Route::get('/business-travelers/{businessTraveler}', [MobilityBusinessTravelerController::class, 'show']);
});
