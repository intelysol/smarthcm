<?php

namespace App\Domains\WorkforceIntelligence\Http\Controllers;

use App\Domains\WorkforceIntelligence\Contracts\KpiOrchestrationInterface;
use App\Domains\WorkforceIntelligence\Contracts\WorkforceCommandCenterInterface;
use App\Domains\WorkforceIntelligence\Contracts\WorkforceIntelligenceAiInterface;
use App\Domains\WorkforceIntelligence\Services\CrossDomainExplanationService;
use App\Domains\WorkforceIntelligence\Services\WorkforceDecisionQueueService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkforceIntelligenceApiController extends Controller
{
    public function __construct(
        protected WorkforceCommandCenterInterface $commandCenter,
        protected KpiOrchestrationInterface $kpiService,
        protected WorkforceIntelligenceAiInterface $aiService,
        protected CrossDomainExplanationService $explanationService,
        protected WorkforceDecisionQueueService $decisionService
    ) {}

    public function executiveScorecard(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $departmentId = $request->query('department_id');
        $periodKey = $request->query('period_key');

        $scorecard = $this->commandCenter->getExecutiveScorecard($tenantId, $departmentId, $periodKey);
        return response()->json($scorecard->toArray());
    }

    public function healthIndex(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $departmentId = $request->query('department_id');
        $periodKey = $request->query('period_key');

        $health = $this->commandCenter->getWorkforceHealthIndex($tenantId, $departmentId, $periodKey);
        return response()->json($health->toArray());
    }

    public function pulse(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $departmentId = $request->query('department_id');

        $pulse = $this->commandCenter->getWorkforcePulse($tenantId, $departmentId);
        return response()->json($pulse->toArray());
    }

    public function risks(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $departmentId = $request->query('department_id');
        $category = $request->query('category');
        $severity = $request->query('severity');

        $risks = $this->commandCenter->getConsolidatedRisks($tenantId, $departmentId, $category, $severity);
        return response()->json(['risks' => $risks]);
    }

    public function alerts(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $departmentId = $request->query('department_id');
        $severity = $request->query('severity');

        $alerts = $this->commandCenter->getPrioritizedAlerts($tenantId, $departmentId, $severity);
        return response()->json(['alerts' => $alerts]);
    }

    public function decisionQueue(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $departmentId = $request->query('department_id');

        $queue = $this->commandCenter->getDecisionQueue($tenantId, $departmentId);
        return response()->json(['decision_queue' => $queue]);
    }

    public function processDecision(Request $request, string $decisionId): JsonResponse
    {
        $status = $request->input('status', 'APPROVED');
        $userId = $request->input('user_id');
        $notes = $request->input('notes');

        $decision = $this->decisionService->processDecision($decisionId, $status, $userId, $notes);
        return response()->json(['decision' => $decision]);
    }

    public function explainAnomaly(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $metricKey = $request->input('metric_key', 'OVERTIME_HOURS');
        $departmentId = $request->input('department_id');

        $explanation = $this->explanationService->explainAnomaly($metricKey, $tenantId, $departmentId);
        return response()->json($explanation->toArray());
    }

    public function askAi(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $prompt = $request->input('prompt', '');
        $userId = $request->input('user_id');
        $departmentId = $request->input('department_id');

        $result = $this->aiService->ask($prompt, $tenantId, $userId, $departmentId);
        return response()->json($result->toArray());
    }
}
