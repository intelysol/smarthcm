<?php
use App\Domains\Documents\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;
Route::prefix('v1/documents')->name('api.v1.documents.')->middleware(['web', 'auth', 'tenant'])->group(function (): void {
    Route::get('/', [DocumentController::class, 'index']); Route::post('/', [DocumentController::class, 'store']); Route::get('{document}', [DocumentController::class, 'show']);
    Route::post('{document}/versions', [DocumentController::class, 'version']); Route::get('{document}/download/{version?}', [DocumentController::class, 'download']);
    Route::post('{document}/status', [DocumentController::class, 'status']);
});
