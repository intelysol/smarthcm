<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Enums\ExceptionStatus;
use App\Domains\Attendance\Enums\ExceptionType;
use App\Domains\Attendance\Models\AttendanceException;
use App\Domains\Attendance\Models\AttendancePolicy;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Employee\Models\Employee;

class AttendanceExceptionEngine
{
    /**
     * Evaluate an attendance session against policies and create/update exceptions.
     *
     * @return array<int, AttendanceException>
     */
    public function evaluateSession(
        AttendanceSession $session,
        ?ShiftDefinition $shift,
        AttendancePolicy $policy,
        bool $isWorkingDay = true,
        bool $isHoliday = false
    ): array {
        $generatedExceptions = [];
        $tenantId = $session->tenant_id;
        $employeeId = $session->employee_id;
        $date = $session->session_date->toDateString();

        // 1. Missing Punch Exception
        if ($session->status === 'incomplete' || ($session->actual_start_time && ! $session->actual_end_time)) {
            $generatedExceptions[] = $this->createOrUpdateException(
                $tenantId,
                $employeeId,
                $session->id,
                $date,
                ExceptionType::MISSING_PUNCH->value,
                'critical',
                "Missing clock-out punch for session on {$date}."
            );
        }

        // 2. Tardiness / Late Exception
        if ($session->late_minutes > $policy->late_threshold_minutes) {
            $generatedExceptions[] = $this->createOrUpdateException(
                $tenantId,
                $employeeId,
                $session->id,
                $date,
                ExceptionType::LATE->value,
                $session->late_minutes >= 60 ? 'warning' : 'info',
                "Late arrival: {$session->late_minutes} minutes late (exceeds {$policy->late_threshold_minutes}m threshold)."
            );
        }

        // 3. Early Departure Exception
        if ($session->early_departure_minutes > $policy->early_departure_threshold_minutes) {
            $generatedExceptions[] = $this->createOrUpdateException(
                $tenantId,
                $employeeId,
                $session->id,
                $date,
                ExceptionType::EARLY_DEPARTURE->value,
                'warning',
                "Early departure: left {$session->early_departure_minutes} minutes before scheduled shift end."
            );
        }

        // 4. Absent / Unauthorized Absence Exception
        if ($isWorkingDay && ! $isHoliday && $session->status === 'absent') {
            $generatedExceptions[] = $this->createOrUpdateException(
                $tenantId,
                $employeeId,
                $session->id,
                $date,
                ExceptionType::ABSENT->value,
                'critical',
                "Unauthorized absence on scheduled working day {$date}."
            );
        }

        // 5. Unexpected Attendance on Rest Day / Holiday
        if (! $isWorkingDay && $session->net_worked_minutes > 0) {
            $type = $isHoliday ? ExceptionType::HOLIDAY_WORK->value : ExceptionType::WEEKEND_WORK->value;
            $name = $isHoliday ? 'Holiday' : 'Rest Day';
            $generatedExceptions[] = $this->createOrUpdateException(
                $tenantId,
                $employeeId,
                $session->id,
                $date,
                $type,
                'info',
                "Worked {$session->net_worked_minutes} minutes on scheduled {$name}."
            );
        }

        // 6. Overtime Exception (if policy requires approval)
        if ($session->overtime_minutes > 0 && $policy->overtime_approval_required && ! $session->is_approved) {
            $generatedExceptions[] = $this->createOrUpdateException(
                $tenantId,
                $employeeId,
                $session->id,
                $date,
                ExceptionType::OVERTIME->value,
                'info',
                "Overtime detected: {$session->overtime_minutes} minutes pending manager approval."
            );
        }

        // 7. Insufficient Working Hours
        if ($isWorkingDay && $session->net_worked_minutes > 0 && $session->net_worked_minutes < $policy->minimum_working_hours_minutes) {
            $generatedExceptions[] = $this->createOrUpdateException(
                $tenantId,
                $employeeId,
                $session->id,
                $date,
                ExceptionType::INSUFFICIENT_HOURS->value,
                'warning',
                "Insufficient hours: worked {$session->net_worked_minutes}m (less than required {$policy->minimum_working_hours_minutes}m minimum)."
            );
        }

        return $generatedExceptions;
    }

    protected function createOrUpdateException(
        string $tenantId,
        string $employeeId,
        string $sessionId,
        string $date,
        string $type,
        string $severity,
        string $explanation
    ): AttendanceException {
        /** @var AttendanceException $exception */
        $exception = AttendanceException::query()->firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'employee_id' => $employeeId,
                'attendance_session_id' => $sessionId,
                'exception_type' => $type,
            ],
            [
                'exception_date' => $date,
                'severity' => $severity,
                'status' => ExceptionStatus::OPEN->value,
                'explanation' => $explanation,
            ]
        );

        return $exception;
    }

    public function resolveException(
        AttendanceException $exception,
        string $resolutionType,
        ?string $notes = null,
        ?int $resolverUserId = null
    ): AttendanceException {
        $exception->update([
            'status' => ExceptionStatus::RESOLVED->value,
            'resolution_type' => $resolutionType,
            'resolution_notes' => $notes,
            'resolved_by' => $resolverUserId,
            'resolved_at' => now(),
        ]);

        return $exception;
    }
}
