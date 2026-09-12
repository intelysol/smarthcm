<?php

namespace App\Domains\EmployeeProfile\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeProfile\Services\OrgChartService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgChartController extends Controller
{
    public function __construct(protected OrgChartService $orgChartService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $roots = $this->orgChartService->getRootNodes($tenantId);

        return response()->json([
            'success' => true,
            'data' => $roots,
        ]);
    }

    public function children(Request $request, string $managerId): JsonResponse
    {
        $depth = (int) $request->input('depth', 1);
        $children = $this->orgChartService->getChildrenNodes($managerId, $depth);

        return response()->json([
            'success' => true,
            'data' => $children,
        ]);
    }

    public function focusedSubtree(Request $request, string $employeeId): JsonResponse
    {
        $employee = Employee::findOrFail($employeeId);
        $subtree = $this->orgChartService->getFocusedSubtree($employee);

        return response()->json([
            'success' => true,
            'data' => $subtree,
        ]);
    }
}
