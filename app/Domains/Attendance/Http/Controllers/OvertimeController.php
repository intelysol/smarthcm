<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\OvertimeRequest;
use App\Domains\Attendance\Requests\OvertimeRequestForm;
use App\Domains\Attendance\Services\OvertimeService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    public function __construct(
        protected OvertimeService $overtimeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $overtimes = OvertimeRequest::query()
            ->where('tenant_id', $user->tenant_id)
            ->with(['employee', 'session', 'requester', 'approver'])
            ->latest()
            ->paginate(30);

        return response()->json($overtimes);
    }

    public function store(OvertimeRequestForm $request): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::query()->where('tenant_id', $user->tenant_id)->findOrFail($request->input('employee_id'));

        $ot = $this->overtimeService->requestOvertime($employee, $request->validated(), $user);

        return response()->json([
            'message' => 'Overtime request submitted.',
            'data' => $ot,
        ], 201);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $ot = OvertimeRequest::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);

        $approved = $this->overtimeService->approveOvertime(
            $ot,
            $user,
            $request->filled('approved_minutes') ? (int) $request->input('approved_minutes') : null
        );

        return response()->json([
            'message' => 'Overtime request approved.',
            'data' => $approved,
        ]);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $ot = OvertimeRequest::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $rejected = $this->overtimeService->rejectOvertime($ot, $user, $request->input('reason'));

        return response()->json([
            'message' => 'Overtime request rejected.',
            'data' => $rejected,
        ]);
    }
}
