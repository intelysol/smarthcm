<?php

namespace App\Domains\WorkforcePlanning\Http\Controllers;

use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforceScenario;
use App\Domains\WorkforcePlanning\Services\WorkforceAiPlanningService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkforceAiPlanningController extends Controller
{
    public function __construct(protected WorkforceAiPlanningService $aiService)
    {
    }

    public function planSummary(Request $request, string $planId): JsonResponse
    {
        $plan = HcmWorkforcePlan::findOrFail($planId);
        $summary = $this->aiService->generatePlanExecutiveSummary($plan);
        return response()->json($summary);
    }

    public function scenarioVariance(Request $request, string $scenarioId): JsonResponse
    {
        $scenario = HcmWorkforceScenario::findOrFail($scenarioId);
        $explanation = $this->aiService->explainScenarioVariance($scenario);
        return response()->json($explanation);
    }

    public function query(Request $request): JsonResponse
    {
        $request->validate(['query' => 'required|string|max:1000']);
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID') ?? '00000000-0000-0000-0000-000000000000';

        $response = $this->aiService->answerPlanningQuery($tenantId, $request->input('query'));
        return response()->json($response);
    }
}
