<?php

namespace App\Domains\Compliance\Http\Controllers;

use App\Domains\Compliance\Services\ComplianceAiAdvisoryService;
use App\Domains\Compliance\Services\ComplianceEvaluationService;
use App\Domains\Compliance\Services\ComplianceReportingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplianceAiController extends Controller
{
    public function __construct(
        protected ComplianceAiAdvisoryService $aiService,
        protected ComplianceEvaluationService $evaluationService,
        protected ComplianceReportingService $reportingService
    ) {}

    public function explainStatus(Request $request, string $employeeId): JsonResponse
    {
        $explanation = $this->aiService->explainComplianceStatus($employeeId);

        return response()->json([
            'success' => true,
            'data' => $explanation,
        ]);
    }

    public function executiveSummary(Request $request): JsonResponse
    {
        $tenantId = (string) $request->user()->tenant_id;
        $stats = $this->reportingService->getComplianceDashboardSummary($tenantId);
        $summary = $this->aiService->summarizeDashboard($stats);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
