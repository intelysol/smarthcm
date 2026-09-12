<?php

use App\Domains\ResponsibleAi\Http\Controllers\ResponsibleAiGovernanceWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->prefix('ai-governance')->group(function () {
    Route::get('/dashboard', [ResponsibleAiGovernanceWebController::class, 'dashboard'])->name('responsible-ai.dashboard');
});
