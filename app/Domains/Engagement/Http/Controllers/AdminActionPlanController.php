<?php

namespace App\Domains\Engagement\Http\Controllers;

use App\Domains\Engagement\Models\EngagementActionItem;
use App\Domains\Engagement\Models\EngagementActionPlan;
use App\Domains\Engagement\Requests\ActionPlanCreateRequest;
use App\Domains\Engagement\Resources\EngagementActionPlanResource;
use App\Domains\Engagement\Services\EngagementActionPlanService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminActionPlanController extends Controller
{
    public function __construct(
        protected EngagementActionPlanService $planService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $plans = EngagementActionPlan::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with(['owner', 'campaign'])
            ->withCount('items')
            ->latest()
            ->get();

        return response()->json(EngagementActionPlanResource::collection($plans));
    }

    public function store(ActionPlanCreateRequest $request): JsonResponse
    {
        $plan = $this->planService->createActionPlan(
            $request->user()->tenant_id,
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Action plan created successfully.',
            'plan' => new EngagementActionPlanResource($plan),
        ], 201);
    }

    public function show(Request $request, EngagementActionPlan $plan): JsonResponse
    {
        $plan->load(['items.owner', 'owner', 'campaign']);

        return response()->json([
            'plan' => new EngagementActionPlanResource($plan),
            'items' => $plan->items,
        ]);
    }

    public function update(Request $request, EngagementActionPlan $plan): JsonResponse
    {
        $updated = $this->planService->updateActionPlan($plan, $request->all(), $request->user()->id);

        return response()->json([
            'message' => 'Action plan updated successfully.',
            'plan' => new EngagementActionPlanResource($updated),
        ]);
    }

    public function addItem(Request $request, EngagementActionPlan $plan): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
        ]);

        $item = $this->planService->addActionItem($plan, $request->all());

        return response()->json([
            'message' => 'Action item added successfully.',
            'item' => $item,
        ], 201);
    }

    public function updateItem(Request $request, EngagementActionItem $item): JsonResponse
    {
        $updated = $this->planService->updateActionItem($item, $request->all());

        return response()->json([
            'message' => 'Action item updated successfully.',
            'item' => $updated,
        ]);
    }
}
