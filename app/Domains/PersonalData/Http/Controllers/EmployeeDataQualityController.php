<?php

namespace App\Domains\PersonalData\Http\Controllers;

use App\Domains\PersonalData\Models\HcmEmployeeDataQualityIssue;
use App\Domains\PersonalData\Models\HcmEmployeeDataQualityResult;
use App\Domains\PersonalData\Services\EmployeeDataQualityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeDataQualityController extends Controller
{
    public function __construct(
        protected EmployeeDataQualityService $qualityService
    ) {}

    /**
     * Show quality score and active issues for an employee.
     */
    public function show(string $employeeId): JsonResponse
    {
        $quality = HcmEmployeeDataQualityResult::where('employee_id', $employeeId)->first();
        if (!$quality) {
            $quality = $this->qualityService->calculateQuality($employeeId);
        }

        $issues = HcmEmployeeDataQualityIssue::where('employee_id', $employeeId)
            ->where('is_resolved', false)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'scores' => $quality,
                'issues' => $issues,
            ],
        ]);
    }

    /**
     * Recalculate quality score.
     */
    public function recalculate(string $employeeId): JsonResponse
    {
        $quality = $this->qualityService->calculateQuality($employeeId);
        $issues = HcmEmployeeDataQualityIssue::where('employee_id', $employeeId)
            ->where('is_resolved', false)
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Employee data quality scores recalculated successfully.',
            'data' => [
                'scores' => $quality,
                'issues' => $issues,
            ],
        ]);
    }

    /**
     * Get tenant-wide data quality summary.
     */
    public function tenantSummary(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id ?? $request->query('tenant_id');
        $summary = $this->qualityService->getTenantQualitySummary($tenantId);

        return response()->json([
            'status' => 'success',
            'data' => $summary,
        ]);
    }
}
