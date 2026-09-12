<?php

namespace App\Domains\WorkforceAdmin\Http\Controllers;

use App\Domains\WorkforceAdmin\Models\OpsBulkOperation;
use App\Domains\WorkforceAdmin\Models\OpsCalendarEvent;
use App\Domains\WorkforceAdmin\Models\OpsChecklistInstance;
use App\Domains\WorkforceAdmin\Models\OpsDataQualityRun;
use App\Domains\WorkforceAdmin\Models\OpsException;
use App\Domains\WorkforceAdmin\Models\OpsQueue;
use App\Domains\WorkforceAdmin\Services\EffectiveDatedChangeMonitoringService;
use App\Domains\WorkforceAdmin\Services\HrOperationsDashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkforceAdminDashboardController extends Controller
{
    public function __construct(
        protected HrOperationsDashboardService $dashboardService,
        protected EffectiveDatedChangeMonitoringService $changeService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $summary = $this->dashboardService->getExecutiveSummary($tenantId);

        $recentExceptions = OpsException::when($tenantId !== 'default', fn ($q) => $q->where('tenant_id', $tenantId))
            ->with(['employee'])
            ->latest()
            ->take(5)
            ->get();

        $recentBulkOps = OpsBulkOperation::when($tenantId !== 'default', fn ($q) => $q->where('tenant_id', $tenantId))
            ->with(['creator'])
            ->latest()
            ->take(5)
            ->get();

        return view('workforce-admin.dashboard', compact('summary', 'recentExceptions', 'recentBulkOps'));
    }

    public function queues(Request $request): View
    {
        $queues = OpsQueue::withCount('items')->latest()->paginate(15);
        return view('workforce-admin.queues.index', compact('queues'));
    }

    public function exceptions(Request $request): View
    {
        $exceptions = OpsException::with(['employee', 'owner'])->latest()->paginate(15);
        return view('workforce-admin.exceptions.index', compact('exceptions'));
    }

    public function bulk(Request $request): View
    {
        $operations = OpsBulkOperation::with(['creator', 'validation'])->latest()->paginate(15);
        return view('workforce-admin.bulk.index', compact('operations'));
    }

    public function dataQuality(Request $request): View
    {
        $runs = OpsDataQualityRun::withCount('results')->latest()->paginate(15);
        return view('workforce-admin.data-quality.index', compact('runs'));
    }

    public function calendar(Request $request): View
    {
        $events = OpsCalendarEvent::with('employee')->latest('event_date')->paginate(15);
        return view('workforce-admin.calendar.index', compact('events'));
    }

    public function checklists(Request $request): View
    {
        $checklists = OpsChecklistInstance::with(['template', 'employee', 'items'])->latest()->paginate(15);
        return view('workforce-admin.checklists.index', compact('checklists'));
    }

    public function changes(Request $request): View
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $changes = $this->changeService->getEffectiveDatedChanges($tenantId);
        return view('workforce-admin.changes.index', compact('changes'));
    }
}
