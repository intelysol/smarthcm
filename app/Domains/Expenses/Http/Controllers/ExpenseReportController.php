<?php

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Expenses\Services\ExpenseAccountingService;
use App\Domains\Expenses\Services\ExpenseReportingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseReportController extends Controller
{
    public function __construct(
        protected ExpenseReportingService $reportingService,
        protected ExpenseAccountingService $accountingService
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $summary = $this->reportingService->getDashboardSummary($tenantId);

        return response()->json($summary);
    }

    public function byDepartment(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $data = $this->reportingService->getDepartmentExpenseBreakdown(
            $tenantId,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json($data);
    }

    public function byCategory(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $data = $this->reportingService->getCategoryExpenseBreakdown($tenantId);

        return response()->json($data);
    }

    public function exportGl(Request $request): JsonResponse
    {
        $user = $request->user();
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $export = $this->accountingService->generateAccountingExport(
            $user->tenant_id,
            $startDate,
            $endDate,
            $user
        );

        return response()->json([
            'message' => 'GL accounting export generated successfully.',
            'data' => $export,
        ]);
    }
}
