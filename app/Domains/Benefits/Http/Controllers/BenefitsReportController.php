<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Services\BenefitsAnalyticsService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BenefitsReportController extends Controller
{
    public function __construct(
        protected BenefitsAnalyticsService $analyticsService
    ) {}

    public function executiveSummary(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $summary = $this->analyticsService->getExecutiveSummary($tenantId);

        return response()->json($summary);
    }

    public function employeeTotalCost(Request $request, Employee $employee): JsonResponse
    {
        $basicSalary = (float) ($request->input('basic_salary') ?? 5000.00);
        $cost = $this->analyticsService->calculateTotalEmploymentCost($employee, $basicSalary);

        return response()->json($cost);
    }
}
