<?php

namespace App\Domains\ResponsibleAi\Http\Controllers;

use App\Domains\ResponsibleAi\Contracts\ResponsibleAiGovernanceInterface;
use App\Domains\ResponsibleAi\Services\AiFairnessMonitoringService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResponsibleAiGovernanceApiController extends Controller
{
    public function __construct(
        protected ResponsibleAiGovernanceInterface $governanceService,
        protected AiFairnessMonitoringService $fairnessService
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $dashboard = $this->governanceService->getGovernanceDashboard($tenantId);
        return response()->json($dashboard);
    }

    public function registerUseCase(Request $request): JsonResponse
    {
        $data = $request->all();
        $data['tenant_id'] = $request->header('X-Tenant-ID', 'default-tenant');
        $useCase = $this->governanceService->registerUseCase($data);
        return response()->json(['use_case' => $useCase->toArray()]);
    }

    public function registerModel(Request $request): JsonResponse
    {
        $data = $request->all();
        $data['tenant_id'] = $request->header('X-Tenant-ID', 'default-tenant');
        $model = $this->governanceService->registerModel($data);
        return response()->json(['model' => $model->toArray()]);
    }

    public function killSwitch(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $userId = $request->header('X-User-ID', 'default-admin');
        $scope = $request->input('scope', 'GLOBAL');
        $target = $request->input('target', 'ALL');
        $reason = $request->input('reason', 'Administrative emergency containment');

        $ks = $this->governanceService->triggerKillSwitch($tenantId, $scope, $target, $reason, $userId);
        return response()->json(['kill_switch' => $ks->toArray()]);
    }

    public function checkEligibility(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $useCase = $request->query('use_case', 'WORKFORCE_COPILOT');
        $model = $request->query('model');

        $result = $this->governanceService->checkExecutionEligibility($tenantId, $useCase, $model);
        return response()->json($result);
    }

    public function logIncident(Request $request): JsonResponse
    {
        $data = $request->all();
        $data['tenant_id'] = $request->header('X-Tenant-ID', 'default-tenant');
        $incident = $this->governanceService->logIncident($data);
        return response()->json(['incident' => $incident->toArray()]);
    }

    public function evaluateFairness(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $metric = $request->input('metric', 'SELECTION_PARITY');
        $sample = $request->input('sample', []);
        $threshold = (float) $request->input('threshold', 10.0);

        $check = $this->fairnessService->evaluateParity($tenantId, $metric, $sample, $threshold);
        return response()->json(['fairness_check' => $check->toArray()]);
    }
}
