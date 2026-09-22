<?php

namespace App\Domains\AiOperations\Http\Controllers;

use App\Domains\AiOperations\Contracts\AiOperationsInterface;
use App\Domains\AiOperations\Models\HcmAiEvalDataset;
use App\Domains\AiOperations\Models\HcmAiImprovementItem;
use App\Domains\AiOperations\Models\HcmAiInteractionTelemetry;
use App\Domains\AiOperations\Models\HcmAiRegression;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiOperationsApiController extends Controller
{
    public function __construct(
        protected AiOperationsInterface $operationsService
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $period = $request->query('period', '30d');
        $dashboard = $this->operationsService->getOperationsDashboard($tenantId, $period);

        return response()->json($dashboard);
    }

    public function telemetry(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $useCase = $request->query('use_case');

        $query = HcmAiInteractionTelemetry::where('tenant_id', $tenantId)->latest();
        if ($useCase) {
            $query->where('use_case_code', $useCase);
        }

        $records = $query->take(50)->get();
        return response()->json(['telemetry' => $records]);
    }

    public function recordTelemetry(Request $request): JsonResponse
    {
        $data = $request->all();
        $data['tenant_id'] = $request->header('X-Tenant-ID', 'default-tenant');
        $telemetry = $this->operationsService->recordTelemetry($data);

        return response()->json(['telemetry' => $telemetry->toArray()], 201);
    }

    public function recordFeedback(Request $request): JsonResponse
    {
        $data = $request->all();
        $data['tenant_id'] = $request->header('X-Tenant-ID', 'default-tenant');
        $feedback = $this->operationsService->recordFeedback($data);

        return response()->json(['feedback' => $feedback->toArray()], 201);
    }

    public function datasets(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $datasets = HcmAiEvalDataset::where('tenant_id', $tenantId)->with('cases')->get();

        return response()->json(['datasets' => $datasets]);
    }

    public function runEvaluation(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $userId = $request->header('X-User-ID');
        $datasetId = $request->input('dataset_id');
        $modelCode = $request->input('model_code', 'GPT-4O-ENTERPRISE');

        $run = $this->operationsService->executeEvaluationRun($tenantId, $datasetId, $modelCode, $userId);
        $regressions = $this->operationsService->detectRegressions($tenantId, $run->id);

        return response()->json([
            'evaluation_run' => $run->toArray(),
            'regressions_detected' => $regressions,
        ], 201);
    }

    public function regressions(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $regressions = HcmAiRegression::where('tenant_id', $tenantId)->latest()->get();

        return response()->json(['regressions' => $regressions]);
    }

    public function improvements(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $items = HcmAiImprovementItem::where('tenant_id', $tenantId)->latest()->get();

        return response()->json(['improvements' => $items]);
    }

    public function createImprovement(Request $request): JsonResponse
    {
        $data = $request->all();
        $data['tenant_id'] = $request->header('X-Tenant-ID', 'default-tenant');
        $item = $this->operationsService->createImprovementItem($data);

        return response()->json(['improvement' => $item->toArray()], 201);
    }

    public function readiness(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $useCase = $request->query('use_case', 'EMPLOYEE_CONCIERGE');
        $readiness = $this->operationsService->calculateProductionReadiness($tenantId, $useCase);

        return response()->json(['production_readiness' => $readiness->toArray()]);
    }

    public function budgets(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $scope = $request->query('scope', 'TENANT');
        $target = $request->query('target', 'ALL');
        $status = $this->operationsService->checkBudgetStatus($tenantId, $scope, $target);

        return response()->json(['budget_status' => $status]);
    }

    public function modelBenchmarks(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $benchmarks = $this->operationsService->compareModelPerformance($tenantId);

        return response()->json(['model_benchmarks' => $benchmarks]);
    }
}
