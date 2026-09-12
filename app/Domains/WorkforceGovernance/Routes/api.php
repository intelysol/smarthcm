<?php

use App\Domains\WorkforceGovernance\Http\Controllers\WorkforceDataGovernanceApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('hcm/data-governance')->middleware('api')->group(function () {
    Route::get('/dashboard', [WorkforceDataGovernanceApiController::class, 'dashboard']);
    Route::get('/assets', [WorkforceDataGovernanceApiController::class, 'listAssets']);
    Route::post('/quality/run', [WorkforceDataGovernanceApiController::class, 'runQuality']);
    Route::post('/issues/{issueId}/assign', [WorkforceDataGovernanceApiController::class, 'assignIssue']);
    Route::post('/issues/{issueId}/resolve', [WorkforceDataGovernanceApiController::class, 'resolveIssue']);
    Route::get('/lineage/{nodeCode}', [WorkforceDataGovernanceApiController::class, 'lineage']);
    Route::post('/reconcile', [WorkforceDataGovernanceApiController::class, 'reconcile']);
    Route::get('/kpis', [WorkforceDataGovernanceApiController::class, 'listKpis']);
    Route::post('/kpis/{kpiId}/certify', [WorkforceDataGovernanceApiController::class, 'certifyKpi']);
    Route::get('/issues/{issueId}/explain', [WorkforceDataGovernanceApiController::class, 'explainIssue']);
});
