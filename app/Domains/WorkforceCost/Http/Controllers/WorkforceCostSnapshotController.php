<?php

namespace App\Domains\WorkforceCost\Http\Controllers;

use App\Domains\WorkforceCost\Models\HcmWorkforceCostSnapshot;
use App\Domains\WorkforceCost\Services\LaborCostAggregationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WorkforceCostSnapshotController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $snapshots = HcmWorkforceCostSnapshot::where('tenant_id', $tenantId)
            ->orderByDesc('period_start')
            ->paginate(20);

        return response()->json($snapshots);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $snapshot = HcmWorkforceCostSnapshot::where('tenant_id', $tenantId)
            ->with('lines')
            ->findOrFail($id);

        return response()->json(['data' => $snapshot]);
    }

    public function store(Request $request, LaborCostAggregationService $service): JsonResponse
    {
        $validated = $request->validate([
            'period_name' => 'required|string|max:50',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'currency' => 'nullable|string|size:3',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $userId = $request->user()?->id;

        $snapshot = $service->buildSnapshot(
            $tenantId,
            $validated['period_name'],
            Carbon::parse($validated['period_start']),
            Carbon::parse($validated['period_end']),
            $validated['currency'] ?? 'USD',
            $userId
        );

        return response()->json(['message' => 'Workforce cost snapshot created successfully', 'data' => $snapshot], 201);
    }
}