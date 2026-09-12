<?php

namespace App\Domains\WorkforceCost\Http\Controllers;

use App\Domains\WorkforceCost\Models\HcmWorkforceCostAllocation;
use App\Domains\WorkforceCost\Models\HcmWorkforceCostAllocationRule;
use App\Domains\WorkforceCost\Models\HcmWorkforceCostLine;
use App\Domains\WorkforceCost\Services\LaborCostAllocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LaborCostAllocationController extends Controller
{
    public function indexRules(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $rules = HcmWorkforceCostAllocationRule::where('tenant_id', $tenantId)->orderBy('priority')->get();
        return response()->json(['data' => $rules]);
    }

    public function storeRule(Request $request, LaborCostAllocationService $service): JsonResponse
    {
        $validated = $request->validate([
            'rule_name' => 'required|string|max:150',
            'allocation_method' => 'required|string',
            'source_type' => 'required|string',
            'source_id' => 'nullable|uuid',
            'targets' => 'required|array|min:1',
            'priority' => 'nullable|integer',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $rule = $service->createRule($tenantId, $validated);

        return response()->json(['message' => 'Allocation rule created successfully', 'data' => $rule], 201);
    }

    public function allocateLine(Request $request, string $lineId, LaborCostAllocationService $service): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $line = HcmWorkforceCostLine::where('tenant_id', $tenantId)->findOrFail($lineId);

        $allocations = $service->allocateCostLine($line);
        return response()->json(['message' => 'Cost line allocated successfully', 'data' => $allocations]);
    }
}