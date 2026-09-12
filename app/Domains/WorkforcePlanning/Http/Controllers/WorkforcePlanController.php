<?php

namespace App\Domains\WorkforcePlanning\Http\Controllers;

use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Requests\CreateWorkforcePlanRequest;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanningSecurityService;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkforcePlanController extends Controller
{
    public function __construct(
        protected WorkforcePlanService $planService,
        protected WorkforcePlanningSecurityService $securityService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $query = HcmWorkforcePlan::query()->where('tenant_id', $tenantId);

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $plans = $query->with(['company', 'department', 'businessUnit', 'owner'])->latest()->paginate(20);
        return response()->json($plans);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $plan = HcmWorkforcePlan::with([
            'periods',
            'assumptions',
            'demandPlans',
            'supplyPlans',
            'positions.budget',
            'hiringPlans',
            'costPlans',
            'scenarios',
        ])->findOrFail($id);

        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $plan);
        }

        return response()->json($plan);
    }

    public function store(CreateWorkforcePlanRequest $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $data = array_merge($request->validated(), ['tenant_id' => $tenantId]);

        $plan = $this->planService->createPlan($data, $request->user()?->id);
        return response()->json($plan, 201);
    }

    public function submit(Request $request, string $id): JsonResponse
    {
        $plan = HcmWorkforcePlan::findOrFail($id);
        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $plan);
        }

        $updated = $this->planService->submitPlan($plan, $request->user()?->id);
        return response()->json($updated);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $plan = HcmWorkforcePlan::findOrFail($id);
        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $plan);
        }

        $updated = $this->planService->approvePlan($plan, $request->user()?->id);
        return response()->json($updated);
    }

    public function lock(Request $request, string $id): JsonResponse
    {
        $plan = HcmWorkforcePlan::findOrFail($id);
        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $plan);
        }

        $updated = $this->planService->lockPlan($plan, $request->user()?->id);
        return response()->json($updated);
    }

    public function createVersion(Request $request, string $id): JsonResponse
    {
        $request->validate(['change_rationale' => 'required|string|max:1000']);
        $plan = HcmWorkforcePlan::findOrFail($id);

        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $plan);
        }

        $updated = $this->planService->createNewVersion($plan, $request->input('change_rationale'), $request->user()?->id);
        return response()->json($updated);
    }
}
