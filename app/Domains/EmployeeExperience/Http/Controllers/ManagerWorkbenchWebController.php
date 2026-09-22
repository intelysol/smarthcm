<?php

namespace App\Domains\EmployeeExperience\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeExperience\Services\ManagerWorkbenchService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManagerWorkbenchWebController extends Controller
{
    public function __construct(
        protected ManagerWorkbenchService $workbenchService
    ) {}

    protected function resolveManager(Request $request): Employee
    {
        $user = $request->user();
        $tenantId = $request->query('tenant_id') ?? $request->header('X-Tenant-ID') ?? $user?->tenant_id;
        $managerId = $request->query('manager_id') ?? $request->header('X-Employee-ID');

        if ($managerId && $tenantId) {
            $emp = Employee::where('tenant_id', $tenantId)->where('id', $managerId)->first();
            if ($emp) {
                return $emp;
            }
        }

        if ($user) {
            if ($user->employee) {
                return $user->employee;
            }
            $emp = Employee::where('user_id', $user->id)->first();
            if ($emp) {
                return $emp;
            }
        }

        // Development/fallback: return first manager or employee with direct reports
        $manager = Employee::whereHas('directReports')->first();
        if ($manager) {
            return $manager;
        }

        $any = Employee::first();
        if ($any) {
            return $any;
        }

        abort(404, 'No manager profile found.');
    }

    public function workbench(Request $request): View
    {
        $manager = $this->resolveManager($request);
        $dashboard = $this->workbenchService->getDashboard($manager);
        $roster = $this->workbenchService->getTeamRoster($manager);

        return view('portal.manager.workbench', compact('manager', 'dashboard', 'roster'));
    }

    public function approvals(Request $request): View
    {
        $manager = $this->resolveManager($request);
        $approvals = $this->workbenchService->getPendingApprovals($manager);

        return view('portal.manager.approvals', compact('manager', 'approvals'));
    }
}
