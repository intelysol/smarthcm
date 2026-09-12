<?php

use App\Domains\EmployeeAi\Http\Controllers\EmployeeAiConciergeWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->prefix('me/ai')->group(function () {
    Route::get('/', [EmployeeAiConciergeWebController::class, 'concierge'])->name('employee-ai.concierge');
});
