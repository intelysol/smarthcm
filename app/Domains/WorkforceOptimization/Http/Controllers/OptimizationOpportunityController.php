<?php

namespace App\Domains\WorkforceOptimization\Http\Controllers;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationOpportunity;
use App\Domains\WorkforceOptimization\Services\WorkforceOpportunityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OptimizationOpportunityController extends Controller
{
    public function __construct(
        protected WorkforceOpportunityService $opportunityService
    ) {}

    public function index(Request $request)
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        $query = HcmWorkforceOptimizationOpportunity::query();

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        if ($cat = $request->query('category')) {
            $query->where('category', $cat);
        }

        $opportunities = $query->latest()->paginate(20);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $opportunities,
            ]);
        }

        return view('workforce_optimization.opportunities', compact('opportunities'));
    }

    public function detect(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->input('tenant_id');
        $detected = $this->opportunityService->detectOpportunities($tenantId);

        return response()->json([
            'success' => true,
            'message' => 'Workforce opportunities detected successfully.',
            'count' => count($detected),
            'data' => $detected,
        ]);
    }
}
