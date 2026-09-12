<?php

use App\Domains\Compliance\Http\Controllers\ComplianceAiController;
use App\Domains\Compliance\Http\Controllers\ComplianceBulkController;
use App\Domains\Compliance\Http\Controllers\ComplianceDashboardController;
use App\Domains\Compliance\Http\Controllers\ComplianceExemptionController;
use App\Domains\Compliance\Http\Controllers\ComplianceRenewalController;
use App\Domains\Compliance\Http\Controllers\ComplianceReportController;
use App\Domains\Compliance\Http\Controllers\ComplianceRequirementController;
use App\Domains\Compliance\Http\Controllers\ComplianceVerificationController;
use App\Domains\Compliance\Http\Controllers\EmployeeComplianceController;
use App\Domains\Compliance\Http\Controllers\ProfessionalLicenseController;
use App\Domains\Compliance\Http\Controllers\VisaRecordController;
use App\Domains\Compliance\Http\Controllers\WorkPermitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HCM Compliance API Routes
|--------------------------------------------------------------------------
|
| Prefix is 'v1/hcm/compliance' because Laravel routes/api.php automatically
| prefixes with 'api/'.
|
*/

Route::middleware('auth')->prefix('v1/hcm/compliance')->group(function () {
    // Dashboard & Stats
    Route::get('/dashboard/stats', [ComplianceDashboardController::class, 'stats']);

    // Requirements Catalog
    Route::get('/requirement-types', [ComplianceRequirementController::class, 'indexTypes']);
    Route::get('/requirements', [ComplianceRequirementController::class, 'index']);
    Route::post('/requirements', [ComplianceRequirementController::class, 'store']);
    Route::get('/requirements/{id}', [ComplianceRequirementController::class, 'show']);
    Route::put('/requirements/{id}', [ComplianceRequirementController::class, 'update']);
    Route::delete('/requirements/{id}', [ComplianceRequirementController::class, 'destroy']);

    // Employee Compliance Obligations
    Route::get('/employees/{employeeId}/compliance', [EmployeeComplianceController::class, 'index']);
    Route::post('/employees/{employeeId}/compliance', [EmployeeComplianceController::class, 'store']);
    Route::get('/employees/{employeeId}/compliance/summary', [EmployeeComplianceController::class, 'summary']);
    Route::post('/employees/{employeeId}/compliance/evaluate', [EmployeeComplianceController::class, 'evaluate']);

    // Work Permits
    Route::get('/work-permits/{employeeId}', [WorkPermitController::class, 'index']);
    Route::post('/work-permits/{employeeId}', [WorkPermitController::class, 'store']);
    Route::put('/work-permits/{id}', [WorkPermitController::class, 'update']);
    Route::delete('/work-permits/{id}', [WorkPermitController::class, 'destroy']);

    // Visa Records
    Route::get('/visas/{employeeId}', [VisaRecordController::class, 'index']);
    Route::post('/visas/{employeeId}', [VisaRecordController::class, 'store']);
    Route::put('/visas/{id}', [VisaRecordController::class, 'update']);
    Route::delete('/visas/{id}', [VisaRecordController::class, 'destroy']);

    // Professional Licenses & Registrations
    Route::get('/licenses', [ProfessionalLicenseController::class, 'index']);
    Route::post('/licenses', [ProfessionalLicenseController::class, 'store']);
    Route::get('/licenses/{id}', [ProfessionalLicenseController::class, 'show']);
    Route::put('/licenses/{id}', [ProfessionalLicenseController::class, 'update']);
    Route::get('/registrations', [ProfessionalLicenseController::class, 'registrations']);
    Route::post('/registrations', [ProfessionalLicenseController::class, 'storeRegistration']);

    // Verification Workflow
    Route::get('/verifications', [ComplianceVerificationController::class, 'index']);
    Route::post('/verifications', [ComplianceVerificationController::class, 'verify']);
    Route::get('/verifications/{type}/{id}/history', [ComplianceVerificationController::class, 'history']);

    // Renewal Workflow
    Route::get('/renewals', [ComplianceRenewalController::class, 'index']);
    Route::post('/renewals', [ComplianceRenewalController::class, 'initiate']);
    Route::put('/renewals/{id}/progress', [ComplianceRenewalController::class, 'updateProgress']);
    Route::post('/renewals/{id}/complete', [ComplianceRenewalController::class, 'complete']);

    // Controlled Exemptions
    Route::get('/exemptions', [ComplianceExemptionController::class, 'index']);
    Route::post('/exemptions', [ComplianceExemptionController::class, 'store']);
    Route::post('/exemptions/{id}/approve', [ComplianceExemptionController::class, 'approve']);
    Route::post('/exemptions/{id}/reject', [ComplianceExemptionController::class, 'reject']);
    Route::post('/exemptions/{id}/revoke', [ComplianceExemptionController::class, 'revoke']);

    // Bulk Operations
    Route::post('/bulk/assign', [ComplianceBulkController::class, 'bulkAssign']);
    Route::post('/bulk/preview', [ComplianceBulkController::class, 'bulkPreview']);

    // Operational Reports
    Route::prefix('reports')->group(function () {
        Route::get('/work-permits', [ComplianceReportController::class, 'workPermits']);
        Route::get('/visas', [ComplianceReportController::class, 'visas']);
        Route::get('/licenses', [ComplianceReportController::class, 'licenses']);
        Route::get('/non-compliant', [ComplianceReportController::class, 'nonCompliant']);
        Route::get('/exemptions', [ComplianceReportController::class, 'exemptions']);
    });

    // AI Advisory & Explanations
    Route::get('/ai/explain/{employeeId}', [ComplianceAiController::class, 'explainStatus']);
    Route::get('/ai/executive-summary', [ComplianceAiController::class, 'executiveSummary']);
});
