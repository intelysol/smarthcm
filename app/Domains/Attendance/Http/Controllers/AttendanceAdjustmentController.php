<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\AttendanceAdjustment;
use App\Domains\Attendance\Requests\AttendanceAdjustmentRequest;
use App\Domains\Attendance\Services\AttendanceAdjustmentService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceAdjustmentController extends Controller
{
    public function __construct(
        protected AttendanceAdjustmentService $adjustmentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $adjustments = AttendanceAdjustment::query()
            ->where('tenant_id', $user->tenant_id)
            ->with(['employee', 'session', 'requester', 'approver'])
            ->latest()
            ->paginate(30);

        return response()->json($adjustments);
    }

    public function store(AttendanceAdjustmentRequest $request): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::query()->where('tenant_id', $user->tenant_id)->findOrFail($request->input('employee_id'));

        $adjustment = $this->adjustmentService->requestAdjustment($employee, $request->validated(), $user);

        return response()->json([
            'message' => 'Attendance adjustment request submitted successfully.',
            'data' => $adjustment,
        ], 201);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $adjustment = AttendanceAdjustment::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);

        $approved = $this->adjustmentService->approveAdjustment($adjustment, $user);

        return response()->json([
            'message' => 'Attendance adjustment approved and session recalculated.',
            'data' => $approved,
        ]);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $adjustment = AttendanceAdjustment::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $rejected = $this->adjustmentService->rejectAdjustment($adjustment, $user, $request->input('reason'));

        return response()->json([
            'message' => 'Attendance adjustment rejected.',
            'data' => $rejected,
        ]);
    }
}
