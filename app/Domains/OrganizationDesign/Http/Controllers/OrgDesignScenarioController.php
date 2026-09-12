<?php

namespace App\Domains\OrganizationDesign\Http\Controllers;

use App\Domains\OrganizationDesign\Models\OrgDesignScenario;
use App\Domains\OrganizationDesign\Services\OrganizationScenarioService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgDesignScenarioController extends Controller
{
    public function __construct(
        protected OrganizationScenarioService $scenarioService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $scenarios = OrgDesignScenario::where('tenant_id', $tenantId)
            ->withCount('nodes')
            ->latest()
            ->get();

        return response()->json($scenarios);
    }

    public function store(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $validated = $request->validate([
            'code' => 'required|string|max:80',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'effective_target_date' => 'nullable|date',
        ]);

        $scenario = $this->scenarioService->createScenario($tenantId, $validated, $request->user());

        return response()->json($scenario, 201);
    }

    public function cloneLive(string $scenarioId): JsonResponse
    {
        $scenario = OrgDesignScenario::findOrFail($scenarioId);
        $clonedCount = $this->scenarioService->cloneLiveStructureToScenario($scenario);

        return response()->json([
            'message' => "Successfully cloned {$clonedCount} organizational nodes into scenario.",
            'cloned_nodes_count' => $clonedCount,
        ]);
    }

    public function addNode(Request $request, string $scenarioId): JsonResponse
    {
        $scenario = OrgDesignScenario::findOrFail($scenarioId);
        $validated = $request->validate([
            'node_type' => 'required|string|in:company,business_unit,division,department,team',
            'parent_scenario_node_id' => 'nullable|uuid',
            'code' => 'required|string|max:80',
            'name' => 'required|string|max:150',
            'metadata_payload' => 'nullable|array',
        ]);

        $node = $this->scenarioService->addScenarioNode($scenario, $validated);

        return response()->json($node, 201);
    }

    public function compare(string $scenarioId): JsonResponse
    {
        $scenario = OrgDesignScenario::with('nodes')->findOrFail($scenarioId);
        $diff = $this->scenarioService->compareScenarioWithLive($scenario);

        return response()->json($diff);
    }
}
