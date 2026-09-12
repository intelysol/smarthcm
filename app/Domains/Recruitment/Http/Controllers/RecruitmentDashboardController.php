<?php

namespace App\Domains\Recruitment\Http\Controllers;

use App\Domains\Recruitment\Models\HcmRecruitmentApplication;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Recruitment\Services\RecruitmentAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecruitmentDashboardController extends Controller
{
    public function __construct(protected RecruitmentAnalyticsService $analyticsService)
    {
    }

    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id ?? '00000000-0000-0000-0000-000000000000';

        $kpis = $this->analyticsService->getRecruitmentKpis($tenantId);
        $funnel = $this->analyticsService->getRecruitmentFunnel($tenantId);

        $activeRequisitions = HcmRecruitmentRequisition::where('tenant_id', $tenantId)
            ->whereIn('status', ['open', 'hiring'])
            ->with(['department', 'position'])
            ->latest()
            ->take(5)
            ->get();

        $kanbanCounts = [
            'new' => HcmRecruitmentApplication::where('tenant_id', $tenantId)->where('status', 'new')->count(),
            'screening' => HcmRecruitmentApplication::where('tenant_id', $tenantId)->where('status', 'screening')->count(),
            'shortlisted' => HcmRecruitmentApplication::where('tenant_id', $tenantId)->where('status', 'shortlisted')->count(),
            'interview' => HcmRecruitmentApplication::where('tenant_id', $tenantId)->where('status', 'interview')->count(),
            'offer' => HcmRecruitmentApplication::where('tenant_id', $tenantId)->where('status', 'offer')->count(),
            'hired' => HcmRecruitmentApplication::where('tenant_id', $tenantId)->where('status', 'hired')->count(),
        ];

        return view('recruitment.index', compact('kpis', 'funnel', 'activeRequisitions', 'kanbanCounts'));
    }
}
