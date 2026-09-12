<?php

namespace App\Domains\Engagement\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Engagement\Models\EngagementActionPlan;
use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Resources\EngagementActionPlanResource;
use App\Domains\Engagement\Resources\EngagementCampaignResource;
use App\Domains\Engagement\Services\EngagementPrivacyService;
use App\Domains\Engagement\Services\EngagementResultService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagerEngagementDashboardController extends Controller
{
    public function __construct(
        protected EngagementResultService $resultService,
        protected EngagementPrivacyService $privacyService
    ) {}

    protected function getManager(Request $request): ?Employee
    {
        return Employee::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('user_id', $request->user()->id)
            ->first();
    }

    public function dashboard(Request $request): JsonResponse
    {
        $manager = $this->getManager($request);
        $tenantId = $request->user()->tenant_id;

        $campaigns = EngagementCampaign::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'closed'])
            ->with(['survey'])
            ->latest()
            ->get();

        $actionPlans = EngagementActionPlan::query()
            ->where('tenant_id', $tenantId)
            ->where('owner_id', $manager?->id)
            ->with(['items'])
            ->get();

        return response()->json([
            'manager' => $manager ? ['id' => $manager->id, 'name' => "{$manager->first_name} {$manager->last_name}"] : null,
            'campaigns' => EngagementCampaignResource::collection($campaigns),
            'action_plans' => EngagementActionPlanResource::collection($actionPlans),
        ]);
    }

    public function getResults(Request $request, EngagementCampaign $campaign): JsonResponse
    {
        abort_if($campaign->tenant_id !== $request->user()->tenant_id, 404);

        $manager = $this->getManager($request);
        $departmentId = $manager?->department_id;

        $results = $this->resultService->getCampaignResults($campaign, $departmentId);

        return response()->json($results);
    }

    public function getActionPlans(Request $request): JsonResponse
    {
        $manager = $this->getManager($request);
        $tenantId = $request->user()->tenant_id;

        $plans = EngagementActionPlan::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($manager) {
                $q->where('owner_id', $manager?->id);
                if ($manager && $manager->department_id) {
                    $q->orWhere(fn ($sub) => $sub->where('scope_type', 'department')->where('scope_id', $manager->department_id));
                }
            })
            ->with(['items'])
            ->get();

        return response()->json(EngagementActionPlanResource::collection($plans));
    }
}
