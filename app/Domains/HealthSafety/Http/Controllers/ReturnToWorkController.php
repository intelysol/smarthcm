<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Http\Controllers;

use App\Domains\HealthSafety\Models\HcmReturnToWorkCase;
use App\Domains\HealthSafety\Services\ReturnToWorkService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnToWorkController extends Controller
{
    public function __construct(
        protected ReturnToWorkService $service
    ) {}

    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|uuid',
            'employee_id' => 'required|uuid',
            'incident_id' => 'nullable|uuid',
            'coordinator_employee_id' => 'nullable|uuid',
            'leave_start_date' => 'required|date',
            'target_return_date' => 'nullable|date|after_or_equal:leave_start_date',
            'work_capacity_status' => 'required|in:totally_disabled,partial_capacity,full_capacity',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $request->user()?->tenant_id ?? 'default';

        $case = $this->service->initiateCase($validated, $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Return-to-Work case initiated successfully.',
            'data' => $case,
        ], 201);
    }

    public function createPlan(Request $request, string $id): JsonResponse
    {
        $case = HcmReturnToWorkCase::findOrFail($id);

        $validated = $request->validate([
            'target_return_date' => 'required|date',
            'phased_plan' => 'required|array',
            'notes' => 'nullable|string',
        ]);

        $updated = $this->service->createPlan($case, $validated, (int) $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Phased Return-to-Work plan established.',
            'data' => $updated,
        ]);
    }

    public function complete(Request $request, string $id): JsonResponse
    {
        $case = HcmReturnToWorkCase::findOrFail($id);

        $validated = $request->validate([
            'actual_return_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $completed = $this->service->completeCase($case, $validated, (int) $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Employee successfully returned to work and case closed.',
            'data' => $completed,
        ]);
    }

    public function employeeCases(Request $request, string $employeeId): JsonResponse
    {
        $cases = $this->service->getEmployeeCases($employeeId);

        return response()->json([
            'status' => 'success',
            'data' => $cases,
        ]);
    }
}
