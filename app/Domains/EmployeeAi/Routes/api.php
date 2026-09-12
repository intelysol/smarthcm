<?php

use App\Domains\EmployeeAi\Http\Controllers\EmployeeAiConciergeApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('hcm/me/ai')->middleware('api')->group(function () {
    Route::post('/sessions', [EmployeeAiConciergeApiController::class, 'startSession']);
    Route::post('/sessions/{sessionId}/chat', [EmployeeAiConciergeApiController::class, 'chat']);
    Route::post('/actions/{actionId}/confirm', [EmployeeAiConciergeApiController::class, 'confirmAction']);
    Route::get('/summary', [EmployeeAiConciergeApiController::class, 'mySummary']);
    Route::get('/suggestions', [EmployeeAiConciergeApiController::class, 'suggestions']);
});
