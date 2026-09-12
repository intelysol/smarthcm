<?php

namespace App\Domains\WorkforceOptimization\Http\Controllers;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationOutcome;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRecommendation;
use App\Domains\WorkforceOptimization\Services\OptimizationOutcomeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OptimizationOutcomeController extends Controller
{
    public function __construct(
        protected OptimizationOutcomeService $outcomeService
    ) {}

    public function index(Request $request)
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        $query = HcmWorkforceOptimizationOutcome::with(['recommendation', 'action']);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $outcomes = $query->latest()->paginate(20);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $outcomes,
            ]);
        }

        return view('workforce_optimization.outcomes', compact('outcomes'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recommendation_id' => 'required|uuid',
            'metric_category' => 'required|string',
            'metric_name' => 'required|string',
            'baseline_value' => 'required|numeric',
            'actual_value' => 'required|numeric',
            'window_days' => 'nullable|integer',
        ]);

        $rec = HcmWorkforceOptimizationRecommendation::findOrFail($validated['recommendation_id']);

        $outcome = $this->outcomeService->measureOutcome(
            recommendation: $rec,
            metricCategory: $validated['metric_category'],
            metricName: $validated['metric_name'],
            baselineValue: (float) $validated['baseline_value'],
            actualValue: (float) $validated['actual_value'],
            windowDays: $validated['window_days'] ?? 30
        );

        return response()->json([
            'success' => true,
            'message' => 'Realized outcome evaluated successfully.',
            'data' => $outcome,
        ], 201);
    }
}
