<?php

namespace App\Domains\WorkforceProductivity\Http\Controllers;

use App\Domains\WorkforceProductivity\Models\HcmProductivityScenario;
use App\Domains\WorkforceProductivity\Services\ProductivityScenarioService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductivityScenarioController extends Controller
{
    public function __construct(
        protected ProductivityScenarioService $scenarioService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        if (! $tenantId) {
            return response()->json(['error' => 'Tenant ID is required'], 400);
        }

        $scenarios = HcmProductivityScenario::where('tenant_id', $tenantId)
            ->latest()
            ->paginate(15);

        return response()->json($scenarios);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|uuid',
            'scenario_name' => 'required|string|max:120',
            'scenario_type' => 'nullable|string',
            'baseline_snapshot_id' => 'nullable|uuid',
            'headcount_delta' => 'required|integer',
            'avg_cost_per_head' => 'required|numeric',
            'avg_monthly_hours_per_head' => 'required|numeric',
            'baseline_hourly_output_rate' => 'required|numeric',
            'value_per_unit' => 'required|numeric',
            'assumptions' => 'nullable|array',
        ]);

        $scenario = $this->scenarioService->simulateScenario(
            tenantId: $validated['tenant_id'],
            scenarioName: $validated['scenario_name'],
            scenarioType: $validated['scenario_type'] ?? 'headcount_change',
            baselineSnapshotId: $validated['baseline_snapshot_id'] ?? null,
            headcountDelta: (int) $validated['headcount_delta'],
            avgCostPerHead: (float) $validated['avg_cost_per_head'],
            avgMonthlyHoursPerHead: (float) $validated['avg_monthly_hours_per_head'],
            baselineHourlyOutputRate: (float) $validated['baseline_hourly_output_rate'],
            valuePerUnit: (float) $validated['value_per_unit'],
            assumptions: $validated['assumptions'] ?? []
        );

        return response()->json(['data' => $scenario], 201);
    }
}
