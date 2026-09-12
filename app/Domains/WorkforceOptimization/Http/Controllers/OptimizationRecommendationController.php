<?php

namespace App\Domains\WorkforceOptimization\Http\Controllers;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRecommendation;
use App\Domains\WorkforceOptimization\Services\WorkforceActionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OptimizationRecommendationController extends Controller
{
    public function __construct(
        protected WorkforceActionService $actionService
    ) {}

    public function index(Request $request)
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        $query = HcmWorkforceOptimizationRecommendation::with(['opportunity', 'factors']);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $recommendations = $query->latest()->paginate(20);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $recommendations,
            ]);
        }

        return view('workforce_optimization.recommendations', compact('recommendations'));
    }

    public function show(string $id): JsonResponse
    {
        $recommendation = HcmWorkforceOptimizationRecommendation::with(['opportunity', 'factors', 'actions', 'feedback'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $recommendation,
        ]);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $rec = HcmWorkforceOptimizationRecommendation::findOrFail($id);
        $action = $this->actionService->approveRecommendation(
            $rec,
            $request->user(),
            $request->input('notes')
        );

        return response()->json([
            'success' => true,
            'message' => 'Recommendation approved and execution action spawned.',
            'data' => [
                'recommendation' => $rec->fresh(),
                'action' => $action,
            ],
        ]);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'reason_code' => 'required|string|max:64',
            'narrative' => 'nullable|string',
        ]);

        $rec = HcmWorkforceOptimizationRecommendation::findOrFail($id);
        $this->actionService->rejectRecommendation(
            $rec,
            $request->user(),
            $validated['reason_code'],
            $validated['narrative'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Recommendation rejected.',
            'data' => $rec->fresh(),
        ]);
    }
}
