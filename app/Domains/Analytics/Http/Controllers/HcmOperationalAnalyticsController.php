<?php

namespace App\Domains\Analytics\Http\Controllers;

use App\Domains\Analytics\Services\HcmTalentAndRecruitmentAnalyticsService;
use App\Domains\Analytics\Services\HcmTimeAndAttendanceAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HcmOperationalAnalyticsController extends Controller
{
    public function __construct(
        protected HcmTimeAndAttendanceAnalyticsService $attendanceService,
        protected HcmTalentAndRecruitmentAnalyticsService $talentService
    ) {}

    public function attendance(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $startDate = $request->input('start_date') ?? now()->startOfMonth()->toDateString();
        $endDate = $request->input('end_date') ?? now()->toDateString();
        $filters = $request->only(['department_id', 'branch_id']);

        $metrics = $this->attendanceService->getAttendanceMetrics($tenantId, $startDate, $endDate, $filters);
        $leave = $this->attendanceService->getLeaveUtilization($tenantId, $startDate, $endDate);

        if ($request->wantsJson()) {
            return response()->json(['attendance' => $metrics, 'leave' => $leave]);
        }

        return view('analytics.attendance', compact('metrics', 'leave', 'startDate', 'endDate'));
    }

    public function recruitment(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $startDate = $request->input('start_date') ?? now()->startOfMonth()->toDateString();
        $endDate = $request->input('end_date') ?? now()->toDateString();

        $funnel = $this->talentService->getRecruitmentFunnel($tenantId, $startDate, $endDate);
        $talent = $this->talentService->getTalentAndSuccessionSummary($tenantId);

        if ($request->wantsJson()) {
            return response()->json(['recruitment' => $funnel, 'talent' => $talent]);
        }

        return view('analytics.recruitment', compact('funnel', 'talent', 'startDate', 'endDate'));
    }
}
