<?php

declare(strict_types=1);

namespace App\Domains\EmployeeExperience\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeExperience\Services\EmployeeDashboardService;
use App\Domains\EmployeeExperience\Services\EmployeeQuickActionService;
use App\Domains\EmployeeExperience\Services\EmployeeRequestService;
use App\Domains\EmployeeExperience\Services\EmployeeTaskService;
use App\Domains\Shared\Enums\WorkspaceType;
use App\Domains\Shared\Services\NavigationRegistry;
use App\Domains\Shared\Services\WorkspaceManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeWorkspaceWebController extends Controller
{
    public function __construct(
        protected WorkspaceManager $workspaceManager,
        protected NavigationRegistry $navigationRegistry,
        protected EmployeeDashboardService $dashboardService,
        protected EmployeeTaskService $taskService,
        protected EmployeeRequestService $requestService,
        protected EmployeeQuickActionService $quickActionService
    ) {}

    protected function resolveEmployee(Request $request): Employee
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

        // Fallback to first employee in tenant
        $emp = Employee::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->first();
        if ($emp) {
            return $emp;
        }

        abort(404, 'No employee record found for employee workspace.');
    }

    public function home(Request $request): View
    {
        $employee = $this->resolveEmployee($request);
        $user = $request->user();

        $dashboard = $this->dashboardService->getDashboard($employee);
        $tasks = $this->taskService->getPendingTasks($employee);
        $requests = $this->requestService->getRecentRequests($employee);
        $quickActions = $this->quickActionService->getAvailableActions($employee);

        $currentWorkspace = WorkspaceType::EMPLOYEE;
        $allowedWorkspaces = $this->workspaceManager->resolveAllowedWorkspaces($user);
        $navigation = $this->navigationRegistry->getNavigationFor($currentWorkspace, $user);

        return view('employee-workspace.home', compact(
            'employee',
            'dashboard',
            'tasks',
            'requests',
            'quickActions',
            'currentWorkspace',
            'allowedWorkspaces',
            'navigation'
        ));
    }
}
