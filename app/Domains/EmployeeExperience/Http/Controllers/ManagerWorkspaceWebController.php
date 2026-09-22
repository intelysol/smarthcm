<?php

declare(strict_types=1);

namespace App\Domains\EmployeeExperience\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeExperience\Services\ManagerWorkbenchService;
use App\Domains\Shared\Enums\WorkspaceType;
use App\Domains\Shared\Services\NavigationRegistry;
use App\Domains\Shared\Services\WorkspaceManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ManagerWorkspaceWebController extends Controller
{
    public function __construct(
        protected WorkspaceManager $workspaceManager,
        protected NavigationRegistry $navigationRegistry,
        protected ManagerWorkbenchService $workbenchService
    ) {}

    protected function resolveManager(Request $request): Employee
    {
        $user = $request->user();
        $tenantId = session('tenant_uuid') ?? $user?->tenant_id;

        if ($user && $user->employee) {
            return $user->employee;
        }

        if ($user) {
            $emp = Employee::where('user_id', $user->id)->first();
            if ($emp) {
                return $emp;
            }
        }

        // Fallback to manager with direct reports or first employee in tenant
        $manager = Employee::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->first();
        if ($manager) {
            return $manager;
        }

        abort(404, 'No employee record found for manager context.');
    }

    public function workbench(Request $request): View
    {
        $manager = $this->resolveManager($request);
        $user = $request->user();

        $dashboard = $this->workbenchService->getDashboard($manager);
        $roster = $this->workbenchService->getTeamRoster($manager);

        $currentWorkspace = WorkspaceType::MANAGER;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($user);
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $user);

        return view('manager-workspace.workbench', compact(
            'manager',
            'dashboard',
            'roster',
            'currentWorkspace',
            'allowedWorkspaces',
            'navigation'
        ));
    }

    public function members(Request $request): View
    {
        $manager = $this->resolveManager($request);
        $user = $request->user();
        $roster = $this->workbenchService->getTeamRoster($manager);

        $currentWorkspace = WorkspaceType::MANAGER;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($user);
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $user);

        return view('manager-workspace.members', compact('manager', 'roster', 'currentWorkspace', 'allowedWorkspaces', 'navigation'));
    }

    public function performance(Request $request): View
    {
        $manager = $this->resolveManager($request);
        $user = $request->user();

        $currentWorkspace = WorkspaceType::MANAGER;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($user);
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $user);

        return view('manager-workspace.performance', compact('manager', 'currentWorkspace', 'allowedWorkspaces', 'navigation'));
    }

    public function analytics(Request $request): View
    {
        $manager = $this->resolveManager($request);
        $user = $request->user();

        $currentWorkspace = WorkspaceType::MANAGER;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($user);
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $user);

        return view('manager-workspace.analytics', compact('manager', 'currentWorkspace', 'allowedWorkspaces', 'navigation'));
    }
}
