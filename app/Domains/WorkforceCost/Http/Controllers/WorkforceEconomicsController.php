<?php

namespace App\Domains\WorkforceCost\Http\Controllers;

use App\Domains\WorkforceCost\Models\HcmWorkforceCostEconomics;
use App\Domains\WorkforceCost\Services\WorkforceEconomicsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WorkforceEconomicsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $economics = HcmWorkforceCostEconomics::where('tenant_id', $tenantId)
            ->orderByDesc('period_start')
            ->paginate(20);

        return response()->json($economics);
    }

    public function calculate(Request $request, WorkforceEconomicsService $service): JsonResponse
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'department_id' => 'nullable|uuid',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $economics = $service->calculateEconomics(
            $tenantId,
            Carbon::parse($validated['period_start']),
            Carbon::parse($validated['period_end']),
            $validated['department_id'] ?? null
        );

        return response()->json(['message' => 'Workforce economics calculated successfully', 'data' => $economics], 201);
    }
}