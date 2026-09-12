<?php

namespace App\Domains\WorkforceOptimization\Http\Controllers;

use App\Domains\WorkforceOptimization\Contracts\WorkforceOptimizationInterface;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRun;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationScenario;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OptimizationScenarioController extends Controller
{
    public function __construct(
        protected WorkforceOptimizationInterface $optimizationService
    ) {}

    public function index(Request $request)
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        $query = HcmWorkforceOptimizationScenario::with(['results', 'run']);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $scenarios = $query->latest()->paginate(15);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $scenarios,
            ]);
        }

        return view('workforce_optimization.scenarios', compact('scenarios'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'run_id' => 'required|uuid',
            'scenario_code' => 'required|string|max:64',
            'name' => 'required|string|max:150',
            'parameters' => 'nullable|array',
        ]);

        $run = HcmWorkforceOptimizationRun::findOrFail($validated['run_id']);

        $scenario = $this->optimizationService->simulateScenario(
            run: $run,
            scenarioCode: $validated['scenario_code'],
            name: $validated['name'],
            parameters: $validated['parameters'] ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'What-if scenario simulated successfully.',
            'data' => $scenario,
        ], 201);
    }

    public function compare(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'scenarios' => 'required|array|min:2',
        ]);

        $comparison = $this->optimizationService->compareScenarios($validated['scenarios']);

        return response()->json([
            'success' => true,
            'data' => $comparison->toArray(),
        ]);
    }
}
