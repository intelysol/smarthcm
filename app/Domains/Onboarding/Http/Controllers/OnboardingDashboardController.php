<?php

namespace App\Domains\Onboarding\Http\Controllers;

use App\Domains\Onboarding\Enums\OnboardingCaseStatus;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Services\OnboardingAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingDashboardController extends Controller
{
    public function __construct(protected OnboardingAnalyticsService $analyticsService)
    {
    }

    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id ?? '00000000-0000-0000-0000-000000000000';

        $kpis = $this->analyticsService->getOnboardingKpis($tenantId);

        $kanbanCases = [
            'preboarding' => HcmOnboardingCase::where('tenant_id', $tenantId)->where('status', OnboardingCaseStatus::PREBOARDING->value)->with('employee')->get(),
            'ready' => HcmOnboardingCase::where('tenant_id', $tenantId)->where('status', OnboardingCaseStatus::READY->value)->with('employee')->get(),
            'in_progress' => HcmOnboardingCase::where('tenant_id', $tenantId)->where('status', OnboardingCaseStatus::IN_PROGRESS->value)->with('employee')->get(),
            'blocked' => HcmOnboardingCase::where('tenant_id', $tenantId)->where('status', OnboardingCaseStatus::BLOCKED->value)->with('employee')->get(),
            'completed' => HcmOnboardingCase::where('tenant_id', $tenantId)->where('status', OnboardingCaseStatus::COMPLETED->value)->with('employee')->get(),
        ];

        $upcomingJoiners = HcmOnboardingCase::where('tenant_id', $tenantId)
            ->whereIn('status', [OnboardingCaseStatus::PREBOARDING->value, OnboardingCaseStatus::READY->value])
            ->with(['employee.department', 'tasks'])
            ->orderBy('start_date')
            ->take(6)
            ->get();

        return view('onboarding.index', compact('kpis', 'kanbanCases', 'upcomingJoiners'));
    }
}
