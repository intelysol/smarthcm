<?php

namespace App\Domains\WorkforceCost\Http\Controllers;

use App\Domains\WorkforceCost\Models\HcmWorkforceCostVariance;
use App\Domains\WorkforceCost\Services\WorkforceCostVarianceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WorkforceCostVarianceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $variances = HcmWorkforceCostVariance::where('tenant_id', $tenantId)
            ->orderByDesc('period_start')
            ->paginate(20);

        return response()->json($variances);
    }

    public function calculate(Request $request, WorkforceCostVarianceService $service): JsonResponse
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'comparison_type' => 'nullable|string',
            'planned_amount' => 'nullable|numeric',
            'department_id' => 'nullable|uuid',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $variance = $service->calculateVariance(
            $tenantId,
            Carbon::parse($validated['period_start']),
            Carbon::parse($validated['period_end']),
            $validated['comparison_type'] ?? 'plan_vs_actual',
            (float) ($validated['planned_amount'] ?? 0),
            $validated['department_id'] ?? null
        );

        return response()->json(['message' => 'Variance calculated successfully', 'data' => $variance], 201);
    }
}