<?php

namespace App\Domains\WorkforceAdmin\Http\Controllers;

use App\Domains\WorkforceAdmin\Models\OpsCalendarEvent;
use App\Domains\WorkforceAdmin\Models\OpsConfigurationHealthCheck;
use App\Domains\WorkforceAdmin\Models\OpsDataQualityRule;
use App\Domains\WorkforceAdmin\Models\OpsDataQualityRun;
use App\Domains\WorkforceAdmin\Models\OpsReconciliationRule;
use App\Domains\WorkforceAdmin\Services\ConfigurationHealthService;
use App\Domains\WorkforceAdmin\Services\CrossDomainReconciliationService;
use App\Domains\WorkforceAdmin\Services\HrCalendarService;
use App\Domains\WorkforceAdmin\Services\HrDataQualityService;
use App\Domains\WorkforceAdmin\Services\WorkforceAdminAiAdvisoryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpsGovernanceAndMonitoringController extends Controller
{
    public function __construct(
        protected HrDataQualityService $dataQualityService,
        protected CrossDomainReconciliationService $reconciliationService,
        protected HrCalendarService $calendarService,
        protected ConfigurationHealthService $healthService,
        protected WorkforceAdminAiAdvisoryService $aiService
    ) {}

    public function runDataQualityScan(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $run = $this->dataQualityService->runQualityScan($tenantId);
        return response()->json($run->load('results'));
    }

    public function runReconciliation(OpsReconciliationRule $rule): JsonResponse
    {
        $results = $this->reconciliationService->reconcile($rule);
        return response()->json($results);
    }

    public function syncCalendar(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $count = $this->calendarService->syncCalendarEvents($tenantId);
        return response()->json(['synced_events' => $count]);
    }

    public function configurationHealth(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $checks = $this->healthService->runDiagnostics($tenantId);
        return response()->json($checks);
    }

    public function aiInsights(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $insights = $this->aiService->generateAdvisoryInsights($tenantId);
        return response()->json($insights);
    }
}
