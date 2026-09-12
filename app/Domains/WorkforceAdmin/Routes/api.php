<?php

use App\Domains\WorkforceAdmin\Http\Controllers\EffectiveDatedChangesController;
use App\Domains\WorkforceAdmin\Http\Controllers\EmployeeOperationsViewController;
use App\Domains\WorkforceAdmin\Http\Controllers\OpsBulkOperationController;
use App\Domains\WorkforceAdmin\Http\Controllers\OpsChecklistController;
use App\Domains\WorkforceAdmin\Http\Controllers\OpsExceptionController;
use App\Domains\WorkforceAdmin\Http\Controllers\OpsGovernanceAndMonitoringController;
use App\Domains\WorkforceAdmin\Http\Controllers\OpsQueueController;
use App\Domains\WorkforceAdmin\Services\HrOperationsDashboardService;
use App\Domains\WorkforceAdmin\Services\IntegrationMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/hcm/workforce-admin')->middleware(['api', 'auth'])->group(function () {
    // 1. Operational Dashboard
    Route::get('/dashboard', function (Request $request, HrOperationsDashboardService $service) {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID') ?? 'default';
        return response()->json($service->getExecutiveSummary($tenantId));
    });

    // 2. Operational Queues & Items
    Route::get('/queues', [OpsQueueController::class, 'index']);
    Route::post('/queues', [OpsQueueController::class, 'store']);
    Route::get('/queues/{queue}/items', [OpsQueueController::class, 'items']);
    Route::post('/queue-items/{item}/assign', [OpsQueueController::class, 'assignItem']);
    Route::post('/queue-items/{item}/complete', [OpsQueueController::class, 'completeItem']);

    // 3. Operational Exceptions
    Route::get('/exceptions', [OpsExceptionController::class, 'index']);
    Route::post('/exceptions', [OpsExceptionController::class, 'store']);
    Route::get('/exceptions/{exception}', [OpsExceptionController::class, 'show']);
    Route::post('/exceptions/{exception}/assign', [OpsExceptionController::class, 'assign']);
    Route::post('/exceptions/{exception}/resolve', [OpsExceptionController::class, 'resolve']);

    // 4. Bulk Operations Engine
    Route::get('/bulk', [OpsBulkOperationController::class, 'index']);
    Route::post('/bulk', [OpsBulkOperationController::class, 'store']);
    Route::get('/bulk/{bulkOperation}', [OpsBulkOperationController::class, 'show']);
    Route::post('/bulk/{bulkOperation}/validate', [OpsBulkOperationController::class, 'validateAndDryRun']);
    Route::post('/bulk/{bulkOperation}/approve', [OpsBulkOperationController::class, 'approve']);
    Route::post('/bulk/{bulkOperation}/execute', [OpsBulkOperationController::class, 'execute']);

    // 5. Governance, Data Quality, Reconciliation, Calendar & AI
    Route::post('/data-quality/scan', [OpsGovernanceAndMonitoringController::class, 'runDataQualityScan']);
    Route::post('/reconciliation/{rule}/run', [OpsGovernanceAndMonitoringController::class, 'runReconciliation']);
    Route::post('/calendar/sync', [OpsGovernanceAndMonitoringController::class, 'syncCalendar']);
    Route::get('/configuration-health', [OpsGovernanceAndMonitoringController::class, 'configurationHealth']);
    Route::get('/ai-insights', [OpsGovernanceAndMonitoringController::class, 'aiInsights']);

    // 6. Operational Checklists
    Route::get('/checklists/templates', [OpsChecklistController::class, 'templates']);
    Route::post('/checklists/templates', [OpsChecklistController::class, 'createTemplate']);
    Route::get('/checklists/instances', [OpsChecklistController::class, 'instances']);
    Route::post('/checklists/templates/{template}/instantiate', [OpsChecklistController::class, 'instantiate']);
    Route::post('/checklists/items/{item}/complete', [OpsChecklistController::class, 'completeItem']);

    // 7. Effective-Dated Changes & Cross-Domain Impact Analysis
    Route::get('/effective-dated-changes', [EffectiveDatedChangesController::class, 'index']);
    Route::post('/impact-analysis', [EffectiveDatedChangesController::class, 'impactAnalysis']);

    // 8. Employee 360° Operational View
    Route::get('/employees/{employee}/operational-view', [EmployeeOperationsViewController::class, 'show']);

    // 9. Integration Monitoring
    Route::get('/integrations/health', function (Request $request, IntegrationMonitoringService $service) {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID') ?? 'default';
        return response()->json($service->getIntegrationHealthSummary($tenantId));
    });
});
