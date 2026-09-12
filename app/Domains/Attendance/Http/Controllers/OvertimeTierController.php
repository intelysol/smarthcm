<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\HcmOvertimeTierRecord;
use App\Domains\Attendance\Services\OvertimeTierCalculationService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OvertimeTierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $query = HcmOvertimeTierRecord::where('tenant_id', $tenantId);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function calculate(Request $request, OvertimeTierCalculationService $service): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'overtime_date' => 'required|date',
            'worked_minutes' => 'required|integer|min:0',
            'standard_daily_minutes' => 'nullable|integer',
            'is_rest_day' => 'nullable|boolean',
            'is_holiday' => 'nullable|boolean',
            'is_pre_approved' => 'nullable|boolean',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');

        $record = $service->calculateDailyOvertimeTiers(
            $tenantId,
            $validated['employee_id'],
            Carbon::parse($validated['overtime_date']),
            $validated['worked_minutes'],
            $validated['standard_daily_minutes'] ?? 480,
            $validated['is_rest_day'] ?? false,
            $validated['is_holiday'] ?? false,
            null,
            $validated['is_pre_approved'] ?? false
        );

        return response()->json(['message' => 'Overtime tiers calculated', 'data' => $record], 201);
    }

    public function approve(Request $request, string $id, OvertimeTierCalculationService $service): JsonResponse
    {
        $approverId = $request->user()?->id ?? 1;
        $record = $service->approveOvertime($id, $approverId);

        return response()->json(['message' => 'Overtime approved', 'data' => $record]);
    }

    public function reject(Request $request, string $id, OvertimeTierCalculationService $service): JsonResponse
    {
        $request->validate(['rejection_reason' => 'required|string']);
        $approverId = $request->user()?->id ?? 1;

        $record = $service->rejectOvertime($id, $approverId, $request->input('rejection_reason'));

        return response()->json(['message' => 'Overtime rejected', 'data' => $record]);
    }
}