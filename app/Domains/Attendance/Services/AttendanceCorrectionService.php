<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Models\AttendanceAdjustment;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\HcmAttendanceCorrectionAudit;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AttendanceCorrectionService
{
    /**
     * Submit an attendance correction request.
     */
    public function requestCorrection(
        string $tenantId,
        string $employeeId,
        string $sessionId,
        string $adjustmentType,
        array $requestedValues,
        string $reason,
        int $requestedById,
        ?string $supportingDocumentId = null
    ): AttendanceAdjustment {
        $session = AttendanceSession::where('tenant_id', $tenantId)->findOrFail($sessionId);

        $originalValues = [
            'actual_start_time' => $session->actual_start_time?->toIso8601String(),
            'actual_end_time' => $session->actual_end_time?->toIso8601String(),
            'net_worked_minutes' => $session->net_worked_minutes,
            'status' => $session->status,
        ];

        $adjustment = AttendanceAdjustment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'employee_id' => $employeeId,
            'attendance_session_id' => $sessionId,
            'adjustment_date' => $session->session_date,
            'adjustment_type' => $adjustmentType,
            'original_values' => $originalValues,
            'requested_values' => $requestedValues,
            'reason' => $reason,
            'supporting_document_id' => $supportingDocumentId,
            'status' => 'pending',
            'requested_by' => $requestedById,
        ]);

        HcmAttendanceCorrectionAudit::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'attendance_adjustment_id' => $adjustment->id,
            'employee_id' => $employeeId,
            'action' => 'requested',
            'actor_id' => $requestedById,
            'before_snapshot' => $originalValues,
            'after_snapshot' => $requestedValues,
            'reason' => $reason,
            'created_at' => now(),
        ]);

        return $adjustment;
    }

    /**
     * Approve correction request and update session non-destructively.
     */
    public function approveCorrection(string $adjustmentId, int $approverId, ?string $notes = null): AttendanceAdjustment
    {
        $adjustment = AttendanceAdjustment::findOrFail($adjustmentId);
        $session = $adjustment->session;

        $requestedValues = $adjustment->requested_values ?? [];

        // Apply corrected times if provided
        if (isset($requestedValues['actual_start_time'])) {
            $session->actual_start_time = Carbon::parse($requestedValues['actual_start_time']);
        }
        if (isset($requestedValues['actual_end_time'])) {
            $session->actual_end_time = Carbon::parse($requestedValues['actual_end_time']);
        }

        if ($session->actual_start_time && $session->actual_end_time) {
            $session->gross_duration_minutes = max(0, $session->actual_start_time->diffInMinutes($session->actual_end_time));
            $session->net_worked_minutes = max(0, $session->gross_duration_minutes - ($session->unpaid_break_minutes ?? 0));
            $session->regular_minutes = min($session->net_worked_minutes, 480);
            $session->overtime_minutes = max(0, $session->net_worked_minutes - 480);
        }

        $session->is_adjusted = true;
        $session->save();

        $adjustment->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        HcmAttendanceCorrectionAudit::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $adjustment->tenant_id,
            'attendance_adjustment_id' => $adjustment->id,
            'employee_id' => $adjustment->employee_id,
            'action' => 'approved',
            'actor_id' => $approverId,
            'before_snapshot' => $adjustment->original_values,
            'after_snapshot' => [
                'actual_start_time' => $session->actual_start_time?->toIso8601String(),
                'actual_end_time' => $session->actual_end_time?->toIso8601String(),
                'net_worked_minutes' => $session->net_worked_minutes,
                'approval_notes' => $notes,
            ],
            'reason' => $notes ?? 'Correction approved by manager',
            'created_at' => now(),
        ]);

        return $adjustment;
    }

    /**
     * Reject correction request.
     */
    public function rejectCorrection(string $adjustmentId, int $approverId, string $rejectionReason): AttendanceAdjustment
    {
        $adjustment = AttendanceAdjustment::findOrFail($adjustmentId);

        $adjustment->update([
            'status' => 'rejected',
            'approved_by' => $approverId,
            'rejection_reason' => $rejectionReason,
            'approved_at' => now(),
        ]);

        HcmAttendanceCorrectionAudit::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $adjustment->tenant_id,
            'attendance_adjustment_id' => $adjustment->id,
            'employee_id' => $adjustment->employee_id,
            'action' => 'rejected',
            'actor_id' => $approverId,
            'before_snapshot' => $adjustment->original_values,
            'after_snapshot' => ['rejection_reason' => $rejectionReason],
            'reason' => $rejectionReason,
            'created_at' => now(),
        ]);

        return $adjustment;
    }
}