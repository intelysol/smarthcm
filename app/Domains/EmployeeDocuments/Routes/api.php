<?php

use App\Domains\EmployeeDocuments\Http\Controllers\EmployeeDocumentAiController;
use App\Domains\EmployeeDocuments\Http\Controllers\EmployeeDocumentAnalyticsController;
use App\Domains\EmployeeDocuments\Http\Controllers\EmployeeDocumentBulkController;
use App\Domains\EmployeeDocuments\Http\Controllers\EmployeeDocumentController;
use App\Domains\EmployeeDocuments\Http\Controllers\EmployeeDocumentRequestController;
use App\Domains\EmployeeDocuments\Http\Controllers\EmployeeDocumentRequirementController;
use App\Domains\EmployeeDocuments\Http\Controllers\EmployeeDocumentSelfServiceController;
use App\Domains\EmployeeDocuments\Http\Controllers\EmployeeDocumentVerificationController;
use Illuminate\Support\Facades\Route;

// 1. Employee Self-Service Document APIs
Route::middleware('auth')->prefix('v1/me/documents')->group(function (): void {
    Route::get('/', [EmployeeDocumentSelfServiceController::class, 'myDocuments']);
    Route::get('requirements', [EmployeeDocumentSelfServiceController::class, 'myRequirements']);
    Route::get('requests', [EmployeeDocumentSelfServiceController::class, 'myRequests']);
    Route::post('{id}/acknowledge', [EmployeeDocumentSelfServiceController::class, 'acknowledge']);
});

// 2. HCM HR & Manager Digital Personnel File APIs
Route::middleware('auth')->prefix('v1/hcm')->group(function (): void {
    // Employee Documents & Personnel File
    Route::get('employees/{employee}/documents', [EmployeeDocumentController::class, 'index']);
    Route::post('employees/{employee}/documents', [EmployeeDocumentController::class, 'store']);
    Route::get('employee-documents/{id}', [EmployeeDocumentController::class, 'show']);
    Route::put('employee-documents/{id}', [EmployeeDocumentController::class, 'update']);
    Route::post('employee-documents/{id}/replace', [EmployeeDocumentController::class, 'replace']);
    Route::get('employee-documents/{id}/download', [EmployeeDocumentController::class, 'download']);

    // Verification Workflow
    Route::post('employee-documents/{id}/verify', [EmployeeDocumentVerificationController::class, 'verify']);
    Route::post('employee-documents/{id}/reject', [EmployeeDocumentVerificationController::class, 'reject']);

    // Requirements & Completeness
    Route::get('employees/{employee}/document-requirements', [EmployeeDocumentRequirementController::class, 'index']);
    Route::post('employees/{employee}/document-requirements', [EmployeeDocumentRequirementController::class, 'assign']);
    Route::post('document-requirements/{id}/waive', [EmployeeDocumentRequirementController::class, 'waive']);

    // Document Requests
    Route::get('document-requests', [EmployeeDocumentRequestController::class, 'index']);
    Route::post('document-requests', [EmployeeDocumentRequestController::class, 'store']);
    Route::post('document-requests/{id}/cancel', [EmployeeDocumentRequestController::class, 'cancel']);

    // Bulk Document Processing
    Route::post('document-bulk/validate', [EmployeeDocumentBulkController::class, 'validateBatch']);
    Route::post('document-bulk/batches/{batchId}/process', [EmployeeDocumentBulkController::class, 'processBatch']);

    // Analytics
    Route::get('document-analytics/metrics', [EmployeeDocumentAnalyticsController::class, 'metrics']);

    // AI Features
    Route::post('documents/ai/extract-metadata', [EmployeeDocumentAiController::class, 'extractMetadata']);
    Route::post('documents/ai/detect-duplicate', [EmployeeDocumentAiController::class, 'detectDuplicate']);
    Route::post('documents/ai/query', [EmployeeDocumentAiController::class, 'query']);
});
