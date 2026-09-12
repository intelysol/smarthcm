<?php

namespace App\Domains\WorkforcePlanning\Http\Controllers;

use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePositionPlan;
use App\Domains\WorkforcePlanning\Requests\CreatePositionPlanRequest;
use App\Domains\WorkforcePlanning\Services\PositionPlanningService;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanningSecurityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PositionPlanController extends Controller
{
    public function __construct(
        protected PositionPlanningService $positionService,
        protected WorkforcePlanningSecurityService $securityService
    ) {
    }

    public function index(Request $request, string $planId): JsonResponse
    {
        $plan = HcmWorkforcePlan::findOrFail($planId);
        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $plan);
        }

        $positions = $plan->positions()->with(['department', 'branch', 'jobGrade', 'budget'])->paginate(25);
        $summary = $this->positionService->getPositionSummary($plan->tenant_id, $plan->id);

        return response()->json([
            'summary' => $summary,
            'positions' => $positions,
        ]);
    }

    public function store(CreatePositionPlanRequest $request, string $planId): JsonResponse
    {
        $plan = HcmWorkforcePlan::findOrFail($planId);
        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $plan);
        }

        $position = $this->positionService->createPosition($plan, $request->validated());
        return response()->json($position, 201);
    }

    public function freeze(Request $request, string $id): JsonResponse
    {
        $position = HcmWorkforcePositionPlan::findOrFail($id);
        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $position->plan);
        }

        $frozen = $this->positionService->freezePosition($position);
        return response()->json($frozen);
    }

    public function unfreeze(Request $request, string $id): JsonResponse
    {
        $position = HcmWorkforcePositionPlan::findOrFail($id);
        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $position->plan);
        }

        $unfrozen = $this->positionService->unfreezePosition($position);
        return response()->json($unfrozen);
    }

    public function eliminate(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $position = HcmWorkforcePositionPlan::findOrFail($id);

        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $position->plan);
        }

        $eliminated = $this->positionService->eliminatePosition($position, $request->input('reason'));
        return response()->json($eliminated);
    }
}
