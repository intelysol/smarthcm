<?php

namespace App\Domains\Compliance\Http\Controllers;

use App\Domains\Compliance\Services\ComplianceReportingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplianceReportController extends Controller
{
    public function __construct(
        protected ComplianceReportingService $reportingService
    ) {}

    public function workPermits(Request $request): JsonResponse
    {
        $tenantId = (string) $request->user()->tenant_id;
        $filters = $request->only(['status', 'expiring_within_days', 'country']);
        $report = $this->reportingService->generateWorkPermitReport($tenantId, $filters);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    public function visas(Request $request): JsonResponse
    {
        $tenantId = (string) $request->user()->tenant_id;
        $filters = $request->only(['status', 'expiring_within_days', 'visa_type']);
        $report = $this->reportingService->generateVisaReport($tenantId, $filters);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    public function licenses(Request $request): JsonResponse
    {
        $tenantId = (string) $request->user()->tenant_id;
        $filters = $request->only(['status', 'expiring_within_days', 'licensing_board']);
        $report = $this->reportingService->generateLicenseReport($tenantId, $filters);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    public function nonCompliant(Request $request): JsonResponse
    {
        $tenantId = (string) $request->user()->tenant_id;
        $report = $this->reportingService->generateNonComplianceReport($tenantId);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    public function exemptions(Request $request): JsonResponse
    {
        $tenantId = (string) $request->user()->tenant_id;
        $report = $this->reportingService->generateExemptionAuditReport($tenantId);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }
}
