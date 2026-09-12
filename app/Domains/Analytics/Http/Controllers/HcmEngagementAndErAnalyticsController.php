<?php

namespace App\Domains\Analytics\Http\Controllers;

use App\Domains\Analytics\Services\HcmAnalyticsSecurityService;
use App\Domains\Analytics\Services\HcmEngagementAndErAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HcmEngagementAndErAnalyticsController extends Controller
{
    public function __construct(
        protected HcmEngagementAndErAnalyticsService $service,
        protected HcmAnalyticsSecurityService $securityService
    ) {}

    public function engagement(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $departmentId = $request->input('department_id');

        $result = $this->service->getEngagementScoreWithPrivacy($tenantId, $departmentId);
        return response()->json($result);
    }

    public function erAggregates(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && ! $this->securityService->canViewErAggregates($user)) {
            abort(403, 'Unauthorized. Access to Employee Relations aggregate analytics requires hcm.analytics.er.aggregate.view permission.');
        }

        $tenantId = $user?->tenant_id ?? 'default';
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $result = $this->service->getErCaseAggregates($tenantId, $startDate, $endDate);
        return response()->json($result);
    }
}
