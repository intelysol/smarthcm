<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Models\AttendanceEvent;
use App\Domains\Attendance\Models\AttendancePeriod;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\AttendanceSessionBreak;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Employee\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AttendanceProcessor
{
    public function __construct(
        protected ShiftResolver $shiftResolver,
        protected AttendancePolicyResolver $policyResolver,
        protected AttendanceSessionBuilder $sessionBuilder,
        protected AttendanceExceptionEngine $exceptionEngine
    ) {}

    /**
     * Deterministically process daily attendance for an employee on a given date.
     */
    public function processEmployeeDate(Employee $employee, string|Carbon $date): AttendanceSession
    {
        $carbonDate = CarbonImmutable::parse($date);
        $dateStr = $carbonDate->toDateString();
        $tenantId = $employee->tenant_id;

        // Verify period lock: If an attendance period is locked for this company/date, reject modification
        $locked = AttendancePeriod::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($employee) {
                $q->where('company_id', $employee->company_id)->orWhereNull('company_id');
            })
            ->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->whereIn('status', ['locked', 'closed'])
            ->exists();

        if ($locked) {
            throw ValidationException::withMessages([
                'attendance_period' => ["Attendance processing locked for {$dateStr} because period is closed."],
            ]);
        }

        // 1. Resolve Planned Shift
        $planned = $this->shiftResolver->resolvePlannedShift($employee, $carbonDate);
        $shift = $planned['shift'];
        $isWorkingDay = $planned['is_working_day'];
        $isHoliday = $planned['is_holiday'];

        // 2. Resolve Applicable Policy
        $policy = $this->policyResolver->resolve($employee, $dateStr);

        // 3. Fetch Normalized Events for this date / shift window
        $events = $this->fetchEventsForShift($employee, $carbonDate, $shift);

        // 4. Build Session Metrics
        $sessionData = $this->sessionBuilder->buildSession(
            $events,
            $shift,
            $policy,
            $dateStr,
            $isWorkingDay,
            $isHoliday
        );

        // 5. Persist Attendance Session
        /** @var AttendanceSession $session */
        $session = AttendanceSession::query()->updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'employee_id' => $employee->id,
                'session_date' => $dateStr,
            ],
            [
                'work_calendar_id' => $employee->company?->work_calendar_id,
                'shift_definition_id' => $shift?->id,
                'roster_assignment_id' => $planned['roster_assignment']?->id,
                'scheduled_start_time' => $shift ? CarbonImmutable::parse("{$dateStr} {$shift->start_time}")->toDateTimeString() : null,
                'scheduled_end_time' => $shift ? $this->getScheduledEndTime($dateStr, $shift) : null,
                'scheduled_minutes' => $shift ? $shift->duration_minutes : 0,
                'actual_start_time' => $sessionData['actual_start_time'],
                'actual_end_time' => $sessionData['actual_end_time'],
                'gross_duration_minutes' => $sessionData['gross_duration_minutes'],
                'total_break_minutes' => $sessionData['total_break_minutes'],
                'unpaid_break_minutes' => $sessionData['unpaid_break_minutes'],
                'paid_break_minutes' => $sessionData['paid_break_minutes'],
                'net_worked_minutes' => $sessionData['net_worked_minutes'],
                'regular_minutes' => $sessionData['regular_minutes'],
                'overtime_minutes' => $sessionData['overtime_minutes'],
                'late_minutes' => $sessionData['late_minutes'],
                'early_departure_minutes' => $sessionData['early_departure_minutes'],
                'undertime_minutes' => $sessionData['undertime_minutes'],
                'status' => $sessionData['status'],
                'is_overnight' => $shift ? $shift->is_night_shift : false,
                'is_split' => $shift ? $shift->is_split : false,
                'is_flexible' => $shift ? $shift->is_flexible : false,
                'processed_at' => now(),
            ]
        );

        // 6. Record Session Breaks
        $session->sessionBreaks()->delete();
        foreach ($sessionData['breaks'] as $b) {
            AttendanceSessionBreak::query()->create([
                'tenant_id' => $tenantId,
                'attendance_session_id' => $session->id,
                'break_start_time' => $b['start'],
                'break_end_time' => $b['end'],
                'duration_minutes' => $b['duration'],
                'is_paid' => $b['is_paid'],
            ]);
        }

        // 7. Evaluate Exceptions
        $this->exceptionEngine->evaluateSession($session, $shift, $policy, $isWorkingDay, $isHoliday);

        return $session;
    }

    /**
     * Process all active employees in a tenant for a given date.
     */
    public function processTenantDate(string $tenantId, string|Carbon $date): Collection
    {
        $employees = Employee::query()
            ->where('tenant_id', $tenantId)
            ->get();

        $processed = collect();
        foreach ($employees as $emp) {
            $processed->push($this->processEmployeeDate($emp, $date));
        }

        return $processed;
    }

    protected function fetchEventsForShift(Employee $employee, CarbonImmutable $date, ?ShiftDefinition $shift): Collection
    {
        $dateStr = $date->toDateString();

        if ($shift && ($shift->is_night_shift || $shift->end_time < $shift->start_time)) {
            // Overnight shift: look from date afternoon to next day noon
            $windowStart = $date->startOfDay()->addHours(12); // 12:00 PM on date
            $windowEnd = $date->addDay()->startOfDay()->addHours(14); // 02:00 PM next day

            return AttendanceEvent::query()
                ->where('tenant_id', $employee->tenant_id)
                ->where('employee_id', $employee->id)
                ->whereBetween('event_timestamp', [$windowStart->toDateTimeString(), $windowEnd->toDateTimeString()])
                ->orderBy('event_timestamp')
                ->get();
        }

        return AttendanceEvent::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->whereDate('local_date', $dateStr)
            ->orderBy('event_timestamp')
            ->get();
    }

    protected function getScheduledEndTime(string $date, ShiftDefinition $shift): string
    {
        $endTime = CarbonImmutable::parse("{$date} {$shift->end_time}");
        if ($shift->is_night_shift || $shift->end_time < $shift->start_time) {
            $endTime = $endTime->addDay();
        }

        return $endTime->toDateTimeString();
    }
}
