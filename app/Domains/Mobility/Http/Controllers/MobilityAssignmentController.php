<?php

namespace App\Domains\Mobility\Http\Controllers;

use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Models\MobilityRequest;
use App\Domains\Mobility\Services\MobilityAssignmentService;
use App\Domains\Mobility\Services\MobilityCostAllocationService;
use App\Domains\Mobility\Services\MobilityCostEstimationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobilityAssignmentController extends Controller
{
    public function __construct(
        protected MobilityAssignmentService $assignmentService,
        protected MobilityCostEstimationService $costEstimationService,
        protected MobilityCostAllocationService $costAllocationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $assignments = MobilityAssignment::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->with(['employee', 'terms', 'locations', 'costs', 'budgets', 'costAllocations'])
            ->latest()
            ->paginate(25);

        return response()->json($assignments);
    }

    public function storeFromRequest(MobilityRequest $mobilityRequest, Request $request): JsonResponse
    {
        $assignment = $this->assignmentService->createFromRequest($mobilityRequest, $request->user());
        return response()->json($assignment, 201);
    }

    public function show(MobilityAssignment $assignment): JsonResponse
    {
        return response()->json($assignment->load([
            'employee', 'terms', 'locations', 'costs', 'budgets', 'costAllocations',
            'relocationCase.items', 'tasks', 'complianceLinks', 'documentLinks',
            'expenseLinks', 'benefitLinks', 'compensationLinks', 'extensions', 'changes', 'repatriation'
        ]));
    }

    public function activate(MobilityAssignment $assignment, Request $request): JsonResponse
    {
        $activated = $this->assignmentService->activateAssignment($assignment, $request->user());
        return response()->json($activated);
    }

    public function complete(MobilityAssignment $assignment, Request $request): JsonResponse
    {
        $completed = $this->assignmentService->completeAssignment($assignment, $request->input('actual_end_date'), $request->user());
        return response()->json($completed);
    }

    public function addCost(MobilityAssignment $assignment, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cost_category' => 'required|string|max:60',
            'cost_name' => 'required|string|max:120',
            'source_amount' => 'required|numeric|min:0',
            'source_currency' => 'sometimes|string|max:10',
            'exchange_rate' => 'sometimes|numeric|min:0.000001',
            'converted_currency' => 'sometimes|string|max:10',
            'frequency' => 'sometimes|string|max:30',
        ]);

        $cost = $this->costEstimationService->addCostItem($assignment, $validated);
        return response()->json($cost, 201);
    }

    public function allocateCosts(MobilityAssignment $assignment, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'allocations' => 'required|array|min:1',
            'allocations.*.entity_role' => 'required|string|in:home,host,shared',
            'allocations.*.company_id' => 'required|uuid',
            'allocations.*.allocation_percentage' => 'required|numeric|min:0|max:100',
            'allocations.*.cost_center_code' => 'nullable|string|max:80',
        ]);

        $allocations = $this->costAllocationService->allocateCosts($assignment, $validated['allocations']);
        return response()->json($allocations);
    }
}
