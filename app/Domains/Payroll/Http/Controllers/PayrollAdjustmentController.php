<?php

namespace App\Domains\Payroll\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\PayrollAdjustment;
use App\Domains\Payroll\Requests\PayrollAdjustmentRequest;
use App\Domains\Payroll\Services\PayrollAdjustmentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollAdjustmentController extends Controller
{
    public function __construct(protected PayrollAdjustmentService $adjustmentService) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $adjustments = PayrollAdjustment::query()
            ->where('tenant_id', $tenantId)
            ->with(['employee', 'period', 'requester', 'approver'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json($adjustments);
    }

    public function store(PayrollAdjustmentRequest $request): JsonResponse
    {
        $employee = Employee::query()->findOrFail($request->input('employee_id'));
        $adj = $this->adjustmentService->requestAdjustment($employee, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Payroll adjustment requested successfully.',
            'data' => $adj,
        ], 201);
    }

    public function approve(PayrollAdjustment $adjustment, Request $request): JsonResponse
    {
        $adj = $this->adjustmentService->approveAdjustment($adjustment, $request->user());

        return response()->json([
            'message' => 'Payroll adjustment approved.',
            'data' => $adj,
        ]);
    }

    public function reject(PayrollAdjustment $adjustment, Request $request): JsonResponse
    {
        $request->validate(['reason' => 'required|string']);
        $adj = $this->adjustmentService->rejectAdjustment($adjustment, $request->user(), $request->input('reason'));

        return response()->json([
            'message' => 'Payroll adjustment rejected.',
            'data' => $adj,
        ]);
    }
}
