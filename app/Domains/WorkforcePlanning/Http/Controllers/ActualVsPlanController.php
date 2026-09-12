<?php

namespace App\Domains\WorkforcePlanning\Http\Controllers;

use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Services\ActualVsPlanAnalyticsService;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanningSecurityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActualVsPlanController extends Controller
{
    public function __construct(
        protected ActualVsPlanAnalyticsService $actualVsPlanService,
        protected WorkforcePlanningSecurityService $securityService
    ) {
    }

    public function matrix(Request $request, string $planId): JsonResponse
    {
        $plan = HcmWorkforcePlan::findOrFail($planId);
        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $plan);
        }

        $departmentId = $request->query('department_id');
        $matrix = $this->actualVsPlanService->getActualVsPlanMatrix($plan, $departmentId);

        return response()->json($matrix);
    }
}
