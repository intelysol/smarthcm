<?php

namespace App\Domains\EmployeeExperience\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeExperience\Services\ManagerWorkbenchService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagerWorkbenchApiController extends Controller
{
    public function __construct(
        protected ManagerWorkbenchService $workbenchService
    ) {}

    protected function resolveManager(Request $request): Employee
    {
        $user = $request->user();
        $tenantId = $request->header('X-Tenant-ID') ?? $user?->tenant_id;

        $employeeId = $request->header('X-Employee-ID');
        if ($employeeId && $tenantId) {
            $emp = Employee::where('tenant_id', $tenantId)->where('id', $employeeId)->first();
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

        abort(401, 'No authenticated manager context found.');
    }

    public function dashboard(Request $request): JsonResponse
    {
        $manager = $this->resolveManager($request);
        $dashboard = $this->workbenchService->getDashboard($manager);

        return response()->json($dashboard);
    }

    public function team(Request $request): JsonResponse
    {
        $manager = $this->resolveManager($request);
        $filters = $request->all();
        $roster = $this->workbenchService->getTeamRoster($manager, $filters);

        return response()->json(['team' => $roster]);
    }

    public function approvals(Request $request): JsonResponse
    {
        $manager = $this->resolveManager($request);
        $approvals = $this->workbenchService->getPendingApprovals($manager);

        return response()->json(['approvals' => $approvals]);
    }

    public function actOnApproval(Request $request, string $type, string $id): JsonResponse
    {
        $manager = $this->resolveManager($request);
        $action = $request->input('action', 'approve');
        $comments = $request->input('comments');

        $result = $this->workbenchService->actOnApproval($manager, $type, $id, $action, $comments);

        return response()->json($result);
    }

    public function alerts(Request $request): JsonResponse
    {
        $manager = $this->resolveManager($request);
        $dash = $this->workbenchService->getDashboard($manager);

        return response()->json(['alerts' => $dash['alerts'] ?? []]);
    }

    public function capacity(Request $request): JsonResponse
    {
        $manager = $this->resolveManager($request);
        $dash = $this->workbenchService->getDashboard($manager);

        return response()->json(['capacity' => $dash['capacity'] ?? []]);
    }
}
