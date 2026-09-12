<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Enums\AdjustmentStatus;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\ExceptionStatus;
use App\Domains\Attendance\Models\AttendanceAdjustment;
use App\Domains\Attendance\Models\AttendancePeriod;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class AttendanceAdjustmentService
{
    public function requestAdjustment(Employee $employee, array $data, User $requester): AttendanceAdjustment
    {
        $date = $data['adjustment_date'];
        $tenantId = $employee->tenant_id;

        // Verify period lock
        $locked = AttendancePeriod::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($employee) {
                $q->where('company_id', $employee->company_id)->orWhereNull('company_id');
            })
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->whereIn('status', ['locked', 'closed'])
            ->exists();

        if ($locked) {
            throw ValidationException::withMessages([
                'adjustment' => ['Cannot request attendance adjustment for a closed/locked period.'],
            ]);
        }

        $session = AttendanceSession::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->where('session_date', $date)
            ->first();

        $originalValues = $session ? [
            'actual_start_time' => $session->actual_start_time?->toDateTimeString(),
            'actual_end_time' => $session->actual_end_time?->toDateTimeString(),
            'net_worked_minutes' => $session->net_worked_minutes,
            'status' => $session->status,
        ] : null;

        return AttendanceAdjustment::query()->create([
            'tenant_id' => $tenantId,
            'employee_id' => $employee->id,
            'attendance_session_id' => $session?->id,
            'adjustment_date' => $date,
            'adjustment_type' => $data['adjustment_type'],
            'original_values' => $originalValues,
            'requested_values' => $data['requested_values'],
            'reason' => $data['reason'],
            'supporting_document_id' => $data['supporting_document_id'] ?? null,
            'status' => AdjustmentStatus::PENDING->value,
            'requested_by' => $requester->id,
        ]);
    }

    public function approveAdjustment(AttendanceAdjustment $adjustment, User $approver): AttendanceAdjustment
    {
        $adjustment->update([
            'status' => AdjustmentStatus::HR_APPROVED->value,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        // Apply corrections to the session
        $this->applyAdjustmentToSession($adjustment, $approver);

        return $adjustment;
    }

    public function rejectAdjustment(AttendanceAdjustment $adjustment, User $approver, string $reason): AttendanceAdjustment
    {
        $adjustment->update([
            'status' => AdjustmentStatus::REJECTED->value,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $adjustment;
    }

    protected function applyAdjustmentToSession(AttendanceAdjustment $adjustment, User $approver): void
    {
        $req = $adjustment->requested_values;
        $date = $adjustment->adjustment_date->toDateString();

        $session = AttendanceSession::query()
            ->where('tenant_id', $adjustment->tenant_id)
            ->where('employee_id', $adjustment->employee_id)
            ->whereDate('session_date', $date)
            ->first();

        if (! $session) {
            $session = AttendanceSession::query()->create([
                'tenant_id' => $adjustment->tenant_id,
                'employee_id' => $adjustment->employee_id,
                'session_date' => $date,
                'status' => AttendanceStatus::PRESENT->value,
            ]);
        }

        $startTime = $req['actual_start_time'] ?? $session->actual_start_time;
        $endTime = $req['actual_end_time'] ?? $session->actual_end_time;

        $workedMinutes = $session->net_worked_minutes;
        if (isset($req['net_worked_minutes'])) {
            $workedMinutes = (int) $req['net_worked_minutes'];
        } elseif ($startTime && $endTime) {
            $start = CarbonImmutable::parse($startTime);
            $end = CarbonImmutable::parse($endTime);
            $workedMinutes = max(0, (int) $start->diffInMinutes($end));
        }

        $session->update([
            'actual_start_time' => $startTime,
            'actual_end_time' => $endTime,
            'net_worked_minutes' => $workedMinutes,
            'regular_minutes' => $workedMinutes,
            'late_minutes' => $req['late_minutes'] ?? 0,
            'early_departure_minutes' => $req['early_departure_minutes'] ?? 0,
            'status' => $req['status'] ?? AttendanceStatus::PRESENT->value,
            'is_adjusted' => true,
            'is_approved' => true,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        // Auto-resolve open exceptions for this session
        $session->exceptions()
            ->where('status', ExceptionStatus::OPEN->value)
            ->update([
                'status' => ExceptionStatus::RESOLVED->value,
                'resolution_type' => 'adjusted',
                'resolution_notes' => 'Resolved via approved attendance adjustment #' . $adjustment->id,
                'resolved_by' => $approver->id,
                'resolved_at' => now(),
            ]);
    }
}
