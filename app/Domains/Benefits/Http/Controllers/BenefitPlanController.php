<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Requests\StoreBenefitPlanRequest;
use App\Domains\Benefits\Services\BenefitPlanService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BenefitPlanController extends Controller
{
    public function __construct(
        protected BenefitPlanService $planService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $plans = BenefitPlan::where('tenant_id', $tenantId)
            ->with(['category', 'provider', 'versions'])
            ->latest()
            ->paginate(25);

        return response()->json($plans);
    }

    public function store(StoreBenefitPlanRequest $request): JsonResponse
    {
        $data = array_merge($request->validated(), [
            'tenant_id' => $request->user()->tenant_id,
            'created_by' => $request->user()->id,
        ]);

        $plan = $this->planService->createPlan($data);
        return response()->json($plan, 201);
    }

    public function show(BenefitPlan $plan): JsonResponse
    {
        return response()->json($plan->load(['category', 'provider', 'versions', 'eligibilityRules']));
    }
}
