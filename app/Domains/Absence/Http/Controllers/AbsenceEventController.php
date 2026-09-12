<?php

namespace App\Domains\Absence\Http\Controllers;

use App\Domains\Absence\Models\HcmAbsenceEvent;
use App\Domains\Absence\Services\AbsenceOrchestrationService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbsenceEventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $query = HcmAbsenceEvent::where('tenant_id', $tenantId)->with(['impacts']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }
        if ($request->filled('is_planned')) {
            $query->where('is_planned', $request->boolean('is_planned'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request, AbsenceOrchestrationService $service): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'absence_date' => 'required|date',
            'duration_hours' => 'required|numeric|min:0.5|max:24',
            'absence_category' => 'nullable|string',
            'source' => 'nullable|string',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'leave_application_id' => 'nullable|uuid',
            'attendance_exception_id' => 'nullable|uuid',
            'is_planned' => 'nullable|boolean',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $userId = $request->user()?->id;

        $event = $service->reportAbsence(
            $tenantId,
            $validated['employee_id'],
            Carbon::parse($validated['absence_date']),
            (float) $validated['duration_hours'],
            $validated['absence_category'] ?? 'unplanned_sick',
            $validated['source'] ?? 'employee_self_service',
            $validated['start_time'] ?? null,
            $validated['end_time'] ?? null,
            $validated['leave_application_id'] ?? null,
            $validated['attendance_exception_id'] ?? null,
            $validated['is_planned'] ?? null,
            $userId
        );

        return response()->json(['message' => 'Absence reported successfully', 'data' => $event], 201);
    }
}