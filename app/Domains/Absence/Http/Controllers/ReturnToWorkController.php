<?php

namespace App\Domains\Absence\Http\Controllers;

use App\Domains\Absence\Models\HcmAbsenceReturnToWorkPlan;
use App\Domains\Absence\Services\ReturnToWorkService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnToWorkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $plans = HcmAbsenceReturnToWorkPlan::where('tenant_id', $tenantId)->get();

        return response()->json(['data' => $plans]);
    }

    public function store(Request $request, ReturnToWorkService $service): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'expected_return_date' => 'required|date',
            'absence_period_id' => 'nullable|uuid',
            'return_phase' => 'nullable|string',
            'capacity_percentage' => 'nullable|numeric|min:0|max:100',
            'operational_restrictions' => 'nullable|array',
            'responsible_manager_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $hrOwnerId = $request->user()?->id;

        $plan = $service->createReturnPlan(
            $tenantId,
            $validated['employee_id'],
            Carbon::parse($validated['expected_return_date']),
            $validated['absence_period_id'] ?? null,
            $validated['return_phase'] ?? 'phased_return',
            (float) ($validated['capacity_percentage'] ?? 50.00),
            $validated['operational_restrictions'] ?? [],
            $validated['responsible_manager_id'] ?? null,
            $hrOwnerId,
            $validated['notes'] ?? null
        );

        return response()->json(['message' => 'Return-to-work plan created', 'data' => $plan], 201);
    }

    public function progress(Request $request, string $id, ReturnToWorkService $service): JsonResponse
    {
        $validated = $request->validate([
            'new_phase' => 'required|string',
            'new_capacity_percentage' => 'required|numeric|min:0|max:100',
            'actual_return_date' => 'nullable|date',
        ]);

        $actualDate = isset($validated['actual_return_date']) ? Carbon::parse($validated['actual_return_date']) : null;

        $plan = $service->progressReturnPlan(
            $id,
            $validated['new_phase'],
            (float) $validated['new_capacity_percentage'],
            $actualDate
        );

        return response()->json(['message' => 'Return-to-work plan progressed', 'data' => $plan]);
    }
}