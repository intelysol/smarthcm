<?php

namespace App\Domains\Engagement\Http\Controllers;

use App\Domains\Engagement\Models\CultureInitiative;
use App\Domains\Engagement\Models\EngagementGoal;
use App\Domains\Engagement\Resources\CultureInitiativeResource;
use App\Domains\Engagement\Services\CultureInitiativeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCultureController extends Controller
{
    public function __construct(
        protected CultureInitiativeService $cultureService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $initiatives = CultureInitiative::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with(['owner'])
            ->withCount('actions')
            ->latest()
            ->get();

        $goals = EngagementGoal::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with('owner')
            ->get();

        return response()->json([
            'initiatives' => CultureInitiativeResource::collection($initiatives),
            'goals' => $goals,
        ]);
    }

    public function storeInitiative(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date'],
            'owner_id' => ['nullable', 'uuid'],
        ]);

        $initiative = $this->cultureService->createInitiative(
            $request->user()->tenant_id,
            $request->all(),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Culture initiative created.',
            'initiative' => new CultureInitiativeResource($initiative),
        ], 201);
    }

    public function storeGoal(Request $request): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'metric_type' => ['required', 'string'],
            'baseline_value' => ['required', 'numeric'],
            'target_value' => ['required', 'numeric'],
            'deadline' => ['required', 'date'],
        ]);

        $goal = $this->cultureService->createGoal(
            $request->user()->tenant_id,
            $request->all()
        );

        return response()->json([
            'message' => 'Engagement goal registered.',
            'goal' => $goal,
        ], 201);
    }

    public function updateGoalProgress(Request $request, EngagementGoal $goal): JsonResponse
    {
        $request->validate([
            'current_value' => ['required', 'numeric'],
        ]);

        $updated = $this->cultureService->updateGoalProgress($goal, (float) $request->input('current_value'));

        return response()->json([
            'message' => 'Goal progress updated.',
            'goal' => $updated,
        ]);
    }
}
