<?php

namespace App\Domains\Compliance\Http\Controllers;

use App\Domains\Compliance\Services\ComplianceEvaluationService;
use App\Domains\Compliance\Services\ComplianceReportingService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplianceDashboardController extends Controller
{
    public function __construct(
        protected ComplianceReportingService $reportingService,
        protected ComplianceEvaluationService $evaluationService
    ) {}

    public function stats(Request $request): JsonResponse
    {
        $tenantId = (string) $request->user()->tenant_id;
        $stats = $this->reportingService->getComplianceDashboardSummary($tenantId);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function webDashboard(Request $request): View
    {
        $tenantId = (string) $request->user()->tenant_id;
        $stats = $this->reportingService->getComplianceDashboardSummary($tenantId);

        return view('compliance.dashboard', compact('stats'));
    }

    public function webEmployeeProfile(Request $request, string $employeeId): View
    {
        $tenantId = (string) $request->user()->tenant_id;
        $evaluation = $this->evaluationService->evaluateEmployee($employeeId);

        return view('compliance.employee_profile', compact('evaluation', 'employeeId'));
    }

    public function webExpirations(Request $request): View
    {
        $tenantId = (string) $request->user()->tenant_id;
        $stats = $this->reportingService->getComplianceDashboardSummary($tenantId);

        return view('compliance.expirations', compact('stats'));
    }

    public function webExemptions(Request $request): View
    {
        $tenantId = (string) $request->user()->tenant_id;
        $stats = $this->reportingService->getComplianceDashboardSummary($tenantId);

        return view('compliance.exemptions', compact('stats'));
    }
}
