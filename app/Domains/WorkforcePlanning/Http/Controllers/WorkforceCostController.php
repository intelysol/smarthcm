<?php

namespace App\Domains\WorkforcePlanning\Http\Controllers;

use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Services\LaborCostPlanningService;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanningSecurityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkforceCostController extends Controller
{
    public function __construct(
        protected LaborCostPlanningService $costService,
        protected WorkforcePlanningSecurityService $securityService
    ) {
    }

    public function index(Request $request, string $planId): JsonResponse
    {
        $plan = HcmWorkforcePlan::findOrFail($planId);

        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $plan);
            $this->securityService->authorizeSensitiveBudgetAccess($request->user());
        }

        $costSummary = $this->costService->calculateTotalLaborCost($plan->tenant_id, $plan->id);
        $costPlans = $plan->costPlans()->with('department')->get();

        return response()->json([
            'summary' => $costSummary,
            'cost_plans' => $costPlans,
        ]);
    }

    public function store(Request $request, string $planId): JsonResponse
    {
        $request->validate([
            'cost_category' => 'required|string|max:50',
            'budgeted_amount' => 'required|numeric|min:0',
            'forecast_amount' => 'nullable|numeric|min:0',
            'actual_amount' => 'nullable|numeric|min:0',
            'department_id' => 'nullable|uuid',
            'currency' => 'nullable|string|max:10',
        ]);

        $plan = HcmWorkforcePlan::findOrFail($planId);

        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $plan);
            $this->securityService->authorizeSensitiveBudgetAccess($request->user());
        }

        $costPlan = $this->costService->recordCostPlan($plan, $request->all());
        return response()->json($costPlan, 201);
    }
}
