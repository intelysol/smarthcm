<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\AttendanceAdjustment;
use App\Domains\Attendance\Services\AttendanceCorrectionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceCorrectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $query = AttendanceAdjustment::where('tenant_id', $tenantId)->with(['audits']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request, AttendanceCorrectionService $service): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'attendance_session_id' => 'required|uuid',
            'adjustment_type' => 'required|string',
            'requested_values' => 'required|array',
            'reason' => 'required|string',
            'supporting_document_id' => 'nullable|string',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $actorId = $request->user()?->id ?? 1;

        $adjustment = $service->requestCorrection(
            $tenantId,
            $validated['employee_id'],
            $validated['attendance_session_id'],
            $validated['adjustment_type'],
            $validated['requested_values'],
            $validated['reason'],
            $actorId,
            $validated['supporting_document_id'] ?? null
        );

        return response()->json(['message' => 'Correction requested', 'data' => $adjustment], 201);
    }

    public function approve(Request $request, string $id, AttendanceCorrectionService $service): JsonResponse
    {
        $actorId = $request->user()?->id ?? 1;
        $notes = $request->input('notes');

        $adjustment = $service->approveCorrection($id, $actorId, $notes);

        return response()->json(['message' => 'Correction approved', 'data' => $adjustment]);
    }

    public function reject(Request $request, string $id, AttendanceCorrectionService $service): JsonResponse
    {
        $request->validate(['rejection_reason' => 'required|string']);
        $actorId = $request->user()?->id ?? 1;

        $adjustment = $service->rejectCorrection($id, $actorId, $request->input('rejection_reason'));

        return response()->json(['message' => 'Correction rejected', 'data' => $adjustment]);
    }
}