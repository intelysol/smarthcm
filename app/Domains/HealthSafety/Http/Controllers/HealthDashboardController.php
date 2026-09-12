<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Http\Controllers;

use App\Domains\HealthSafety\Services\HealthReportingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthDashboardController extends Controller
{
    public function __construct(
        protected HealthReportingService $reportingService
    ) {}

    public function metrics(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $companyId = $request->query('company_id');

        $metrics = $this->reportingService->getDashboardMetrics($tenantId, $companyId);

        return response()->json([
            'status' => 'success',
            'data' => $metrics,
        ]);
    }

    public function oshaLog(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $year = (string) $request->query('year', date('Y'));
        $companyId = $request->query('company_id');

        $osha300 = $this->reportingService->generateOsha300Log($tenantId, $year, $companyId);

        return response()->json([
            'status' => 'success',
            'data' => $osha300,
        ]);
    }
}
