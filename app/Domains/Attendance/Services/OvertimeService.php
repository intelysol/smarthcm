<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Enums\OvertimeStatus;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\OvertimeRequest;
use App\Domains\Employee\Models\Employee;
use App\Models\User;

class OvertimeService
{
    public function requestOvertime(Employee $employee, array $data, User $requester): OvertimeRequest
    {
        $tenantId = $employee->tenant_id;
        $date = $data['overtime_date'];

        $session = AttendanceSession::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->whereDate('session_date', $date)
            ->first();

        $calculatedMinutes = $session ? $session->overtime_minutes : ($data['calculated_overtime_minutes'] ?? 0);
        $requestedMinutes = $data['requested_overtime_minutes'] ?? $calculatedMinutes;

        return OvertimeRequest::query()->create([
            'tenant_id' => $tenantId,
            'employee_id' => $employee->id,
            'attendance_session_id' => $session?->id,
            'overtime_date' => $date,
            'calculated_overtime_minutes' => $calculatedMinutes,
            'requested_overtime_minutes' => $requestedMinutes,
            'overtime_type' => $data['overtime_type'] ?? 'regular_ot',
            'reason' => $data['reason'] ?? null,
            'status' => OvertimeStatus::PENDING->value,
            'requested_by' => $requester->id,
        ]);
    }

    public function approveOvertime(OvertimeRequest $request, User $approver, ?int $approvedMinutes = null): OvertimeRequest
    {
        $minutes = $approvedMinutes ?? $request->requested_overtime_minutes;

        $request->update([
            'approved_overtime_minutes' => $minutes,
            'status' => $minutes < $request->requested_overtime_minutes ? OvertimeStatus::PARTIALLY_APPROVED->value : OvertimeStatus::APPROVED->value,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        if ($request->session) {
            $request->session->update([
                'approved_overtime_minutes' => $minutes,
                'is_approved' => true,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);
        }

        return $request;
    }

    public function rejectOvertime(OvertimeRequest $request, User $approver, string $reason): OvertimeRequest
    {
        $request->update([
            'approved_overtime_minutes' => 0,
            'status' => OvertimeStatus::REJECTED->value,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $request;
    }
}
