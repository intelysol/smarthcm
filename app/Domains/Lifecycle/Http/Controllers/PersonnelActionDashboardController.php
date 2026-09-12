<?php

namespace App\Domains\Lifecycle\Http\Controllers;

use App\Domains\Lifecycle\Enums\PersonnelActionStatus;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Services\PersonnelActionAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonnelActionDashboardController extends Controller
{
    public function __construct(protected PersonnelActionAnalyticsService $analyticsService)
    {
    }

    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id ?? '00000000-0000-0000-0000-000000000000';

        $metrics = $this->analyticsService->getLifecycleMetrics($tenantId);

        $pendingApproval = PersonnelActionRequest::where('tenant_id', $tenantId)
            ->whereIn('status', [PersonnelActionStatus::SUBMITTED->value, PersonnelActionStatus::PENDING_APPROVAL->value])
            ->with(['employee.department', 'actionType'])
            ->latest()
            ->take(5)
            ->get();

        $scheduledActions = PersonnelActionRequest::where('tenant_id', $tenantId)
            ->where('status', PersonnelActionStatus::SCHEDULED->value)
            ->with(['employee.department', 'actionType'])
            ->orderBy('effective_date')
            ->take(5)
            ->get();

        $recentExecuted = PersonnelActionRequest::where('tenant_id', $tenantId)
            ->where('status', PersonnelActionStatus::EXECUTED->value)
            ->with(['employee.department', 'actionType'])
            ->latest('executed_at')
            ->take(5)
            ->get();

        return view('lifecycle.index', compact('metrics', 'pendingApproval', 'scheduledActions', 'recentExecuted'));
    }
}
