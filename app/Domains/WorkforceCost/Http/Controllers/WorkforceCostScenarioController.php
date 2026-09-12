<?php

namespace App\Domains\WorkforceCost\Http\Controllers;

use App\Domains\WorkforceCost\Models\HcmWorkforceCostScenario;
use App\Domains\WorkforceCost\Services\WorkforceCostScenarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WorkforceCostScenarioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $scenarios = HcmWorkforceCostScenario::where('tenant_id', $tenantId)->orderByDesc('created_at')->get();
        return response()->json(['data' => $scenarios]);
    }

    public function store(Request $request, WorkforceCostScenarioService $service): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'scenario_type' => 'required|string',
            'current_cost' => 'required|numeric|min:0',
            'parameters' => 'nullable|array',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $userId = $request->user()?->id;

        $scenario = $service->calculateScenario(
            $tenantId,
            $validated['name'],
            $validated['scenario_type'],
            (float) $validated['current_cost'],
            $validated['parameters'] ?? [],
            $userId
        );

        return response()->json(['message' => 'Workforce cost scenario calculated successfully', 'data' => $scenario], 201);
    }
}