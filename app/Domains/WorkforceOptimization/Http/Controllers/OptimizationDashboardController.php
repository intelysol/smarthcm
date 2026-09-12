<?php

namespace App\Domains\WorkforceOptimization\Http\Controllers;

use App\Domains\WorkforceOptimization\Contracts\WorkforceOptimizationInterface;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationOpportunity;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRecommendation;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRun;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OptimizationDashboardController extends Controller
{
    public function __construct(
        protected WorkforceOptimizationInterface $optimizationService
    ) {}

    public function index(Request $request)
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');

        $latestRuns = HcmWorkforceOptimizationRun::latest('created_at')->limit(5)->get();
        $openOpportunitiesCount = HcmWorkforceOptimizationOpportunity::where('status', 'OPEN')->count();
        $pendingRecommendations = HcmWorkforceOptimizationRecommendation::where('status', 'GENERATED')->limit(10)->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'latest_runs' => $latestRuns,
                    'open_opportunities_count' => $openOpportunitiesCount,
                    'pending_recommendations' => $pendingRecommendations,
                ],
            ]);
        }

        return view('workforce_optimization.dashboard', compact(
            'latestRuns',
            'openOpportunitiesCount',
            'pendingRecommendations'
        ));
    }
}
