<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Services\PortalService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagerTeamController extends Controller
{
    public function index(Request $request, PortalService $portal): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('manager.team'), 403);
        $team = $portal->teamFor($portal->employeeFor($user));

        if ($request->filled('search')) {
            $search = strtolower((string) $request->input('search'));
            $team = $team->filter(fn (Employee $employee) => str_contains(strtolower($employee->fullName()), $search))->values();
        }

        return response()->json(['data' => $team]);
    }

    public function show(Request $request, PortalService $portal, string $employee): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('manager.team'), 403);
        $manager = $portal->employeeFor($user);
        $record = Employee::query()->where('tenant_id', $manager->tenant_id)->where('reporting_manager_id', $manager->id)->with(['department', 'designation', 'branch', 'reportingManager'])->findOrFail($employee);

        return response()->json(['data' => $record]);
    }
}
