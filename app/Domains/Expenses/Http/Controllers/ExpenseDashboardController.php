<?php

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Models\TravelRequest;
use App\Domains\Expenses\Services\ExpenseReportingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseDashboardController extends Controller
{
    public function __construct(
        protected ExpenseReportingService $reportingService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $summary = $this->reportingService->getDashboardSummary($tenantId);

        $recentClaims = ExpenseClaim::where('tenant_id', $tenantId)
            ->with(['employee', 'travelAuthorization'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentTravel = TravelRequest::where('tenant_id', $tenantId)
            ->with(['employee'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'summary' => $summary,
                'recent_claims' => $recentClaims,
                'recent_travel' => $recentTravel,
            ]);
        }

        return view('expenses.dashboard', compact('summary', 'recentClaims', 'recentTravel'));
    }
}
