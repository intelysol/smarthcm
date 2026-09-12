<?php

namespace App\Domains\Analytics\Http\Controllers;

use App\Domains\Analytics\Services\HcmAnalyticsSecurityService;
use App\Domains\Analytics\Services\HcmWorkforceAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HcmWorkforceAnalyticsController extends Controller
{
    public function __construct(
        protected HcmWorkforceAnalyticsService $workforceService,
        protected HcmAnalyticsSecurityService $securityService
    ) {}

    public function headcount(Request $request): View|JsonResponse
    {
        $user = $request->user();
        $tenantId = $user?->tenant_id ?? 'default';
        $asOfDate = $request->input('as_of_date') ?? now()->toDateString();
        
        $filters = $request->only(['department_id', 'branch_id', 'company_id']);
        if ($user) {
            $filters = $this->securityService->applyManagerScopeFilters($user, $filters);
        }

        $summary = $this->workforceService->getHeadcountSummary($tenantId, $asOfDate, $filters);

        if ($request->wantsJson()) {
            return response()->json($summary);
        }

        return view('analytics.workforce', compact('summary', 'asOfDate'));
    }

    public function turnover(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenantId = $user?->tenant_id ?? 'default';
        $startDate = $request->input('start_date') ?? now()->startOfYear()->toDateString();
        $endDate = $request->input('end_date') ?? now()->toDateString();

        $filters = $request->only(['department_id', 'branch_id']);
        if ($user) {
            $filters = $this->securityService->applyManagerScopeFilters($user, $filters);
        }

        $turnover = $this->workforceService->getTurnoverAnalytics($tenantId, $startDate, $endDate, $filters);

        return response()->json($turnover);
    }
}
