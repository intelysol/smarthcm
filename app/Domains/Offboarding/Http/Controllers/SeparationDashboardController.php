<?php

namespace App\Domains\Offboarding\Http\Controllers;

use App\Domains\Offboarding\Enums\SeparationStatus;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Services\SeparationAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeparationDashboardController extends Controller
{
    public function __construct(protected SeparationAnalyticsService $analyticsService)
    {
    }

    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id ?? '00000000-0000-0000-0000-000000000000';

        $metrics = $this->analyticsService->getOffboardingMetrics($tenantId);

        $pendingApprovals = SeparationRequest::where('tenant_id', $tenantId)
            ->whereIn('status', [SeparationStatus::SUBMITTED->value, SeparationStatus::PENDING_APPROVAL->value])
            ->with(['employee.department', 'separationType'])
            ->latest()
            ->take(5)
            ->get();

        $inNoticePeriod = SeparationRequest::where('tenant_id', $tenantId)
            ->where('status', SeparationStatus::NOTICE_PERIOD->value)
            ->with(['employee.department', 'separationType'])
            ->orderBy('approved_last_working_day')
            ->take(5)
            ->get();

        $recentExits = SeparationRequest::where('tenant_id', $tenantId)
            ->where('status', SeparationStatus::EXITED->value)
            ->with(['employee.department', 'separationType'])
            ->latest('executed_at')
            ->take(5)
            ->get();

        return view('offboarding.index', compact('metrics', 'pendingApprovals', 'inNoticePeriod', 'recentExits'));
    }
}
