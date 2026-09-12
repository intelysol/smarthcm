<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\RetirementPlan;
use App\Domains\Benefits\Requests\StoreRetirementPlanRequest;
use App\Domains\Benefits\Services\RetirementPlanService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RetirementPlanController extends Controller
{
    public function __construct(
        protected RetirementPlanService $planService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $plans = RetirementPlan::where('tenant_id', $tenantId)
            ->with(['versions', 'vestingRules'])
            ->latest()
            ->paginate(25);

        return response()->json($plans);
    }

    public function store(StoreRetirementPlanRequest $request): JsonResponse
    {
        $data = array_merge($request->validated(), [
            'tenant_id' => $request->user()->tenant_id,
            'created_by' => $request->user()->id,
        ]);

        $plan = $this->planService->createPlan($data);
        return response()->json($plan, 201);
    }
}
