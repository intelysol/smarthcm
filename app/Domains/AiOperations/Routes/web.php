<?php

use App\Domains\AiOperations\Http\Controllers\AiOperationsWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->prefix('ai-operations')->group(function () {
    Route::get('/dashboard', [AiOperationsWebController::class, 'dashboard'])->name('ai-operations.dashboard');
});
