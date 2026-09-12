<?php

namespace App\Domains\WorkforceGovernance\Http\Controllers;

use App\Domains\WorkforceGovernance\Contracts\WorkforceDataGovernanceInterface;
use App\Domains\WorkforceGovernance\Services\AiDataGovernanceAssistantService;
use App\Domains\WorkforceGovernance\Services\DataAssetCatalogService;
use App\Domains\WorkforceGovernance\Services\DataContractGovernanceService;
use App\Domains\WorkforceGovernance\Services\DataLineageService;
use App\Domains\WorkforceGovernance\Services\DataQualityEngineService;
use App\Domains\WorkforceGovernance\Services\KpiRegistryGovernanceService;
use App\Domains\WorkforceGovernance\Services\MasterDataReconciliationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkforceDataGovernanceApiController extends Controller
{
    public function __construct(
        protected WorkforceDataGovernanceInterface $governanceService,
        protected DataAssetCatalogService $catalogService,
        protected DataQualityEngineService $qualityEngine,
        protected DataLineageService $lineageService,
        protected MasterDataReconciliationService $reconciliationService,
        protected KpiRegistryGovernanceService $kpiRegistryService,
        protected DataContractGovernanceService $contractService,
        protected AiDataGovernanceAssistantService $aiService
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $data = $this->governanceService->getGovernanceDashboard($tenantId);
        return response()->json($data);
    }

    public function listAssets(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $domain = $request->query('domain');
        $assets = $this->catalogService->listAssets($tenantId, $domain);
        return response()->json(['assets' => $assets]);
    }

    public function runQuality(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $domain = $request->input('domain');
        $run = $this->governanceService->executeQualityRun($tenantId, $domain);
        return response()->json(['quality_run' => $run->toArray()]);
    }

    public function assignIssue(Request $request, string $issueId): JsonResponse
    {
        $stewardId = $request->input('steward_id');
        $issue = $this->governanceService->assignIssue($issueId, $stewardId);
        return response()->json(['issue' => $issue->toArray()]);
    }

    public function resolveIssue(Request $request, string $issueId): JsonResponse
    {
        $userId = $request->input('user_id', 'system');
        $explanation = $request->input('explanation');
        $issue = $this->governanceService->resolveIssue($issueId, $userId, $explanation);
        return response()->json(['issue' => $issue->toArray()]);
    }

    public function lineage(Request $request, string $nodeCode): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $lineage = $this->governanceService->getLineageGraph($tenantId, $nodeCode);
        return response()->json($lineage);
    }

    public function reconcile(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $records = $request->input('records', []);
        $result = $this->reconciliationService->reconcileEmployeeHeadcount($tenantId, $records);
        return response()->json(['reconciliation' => $result->toArray()]);
    }

    public function listKpis(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $status = $request->query('status');
        $kpis = $this->kpiRegistryService->listKpis($tenantId, $status);
        return response()->json(['kpis' => $kpis]);
    }

    public function certifyKpi(Request $request, string $kpiId): JsonResponse
    {
        $userId = $request->input('user_id', 'system-admin');
        $kpi = $this->kpiRegistryService->certifyKpi($kpiId, $userId);
        return response()->json(['kpi' => $kpi->toArray()]);
    }

    public function explainIssue(string $issueId): JsonResponse
    {
        $explanation = $this->aiService->explainIssue($issueId);
        return response()->json($explanation);
    }
}
