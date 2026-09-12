<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Models\AttendanceEvent;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\ShiftDefinition;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CrossMidnightAttendanceService
{
    /**
     * Determine operational shift date for a given punch.
     * If OUT event occurs in the early morning (e.g. before 12:00) and an open session exists
     * from previous evening, associate it with the previous evening's operational date.
     */
    public function resolveOperationalDate(
        string $tenantId,
        string $employeeId,
        Carbon $punchTimestamp,
        string $eventType,
        ?ShiftDefinition $scheduledShift = null
    ): Carbon {
        $calendarDate = $punchTimestamp->copy()->startOfDay();

        // If it's an OUT punch and happens in the morning (e.g., before 12:00)
        if (strtoupper($eventType) === 'OUT' && $punchTimestamp->hour < 12) {
            $lookbackStart = $punchTimestamp->copy()->subHours(18);

            $openSession = AttendanceSession::where('tenant_id', $tenantId)
                ->where('employee_id', $employeeId)
                ->whereNull('actual_end_time')
                ->where('actual_start_time', '>=', $lookbackStart)
                ->where('actual_start_time', '<=', $punchTimestamp)
                ->orderByDesc('actual_start_time')
                ->first();

            if ($openSession) {
                return Carbon::parse($openSession->session_date);
            }
        }

        // If scheduled shift is overnight (start time > end time)
        if ($scheduledShift && $this->isOvernightShift($scheduledShift)) {
            if ($punchTimestamp->hour < 12) {
                return $calendarDate->subDay();
            }
        }

        return $calendarDate;
    }

    public function isOvernightShift(ShiftDefinition $shift): bool
    {
        if (! $shift->start_time || ! $shift->end_time) {
            return false;
        }

        return strcmp($shift->start_time, $shift->end_time) > 0;
    }

    /**
     * Process overnight punch pairing.
     */
    public function pairOvernightPunches(
        string $tenantId,
        string $employeeId,
        Carbon $inTimestamp,
        Carbon $outTimestamp,
        ?ShiftDefinition $shift = null,
        int $unpaidBreakMinutes = 0
    ): AttendanceSession {
        $operationalDate = $inTimestamp->copy()->startOfDay();
        $isOvernight = $outTimestamp->toDateString() !== $inTimestamp->toDateString()
            || ($shift && $this->isOvernightShift($shift));

        $grossMinutes = max(0, $inTimestamp->diffInMinutes($outTimestamp));
        $breakMins = $unpaidBreakMinutes > 0 ? $unpaidBreakMinutes : (($shift && isset($shift->unpaid_break_minutes)) ? (int) $shift->unpaid_break_minutes : 0);
        $netMinutes = max(0, $grossMinutes - $breakMins);

        $regularMinutes = min($netMinutes, 480);
        $overtimeMinutes = max(0, $netMinutes - 480);

        return AttendanceSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'employee_id' => $employeeId,
            'session_date' => $operationalDate->toDateString(),
            'shift_definition_id' => $shift?->id,
            'scheduled_start_time' => $shift ? $operationalDate->copy()->setTimeFromTimeString($shift->start_time) : null,
            'scheduled_end_time' => $shift ? ($isOvernight ? $operationalDate->copy()->addDay()->setTimeFromTimeString($shift->end_time) : $operationalDate->copy()->setTimeFromTimeString($shift->end_time)) : null,
            'scheduled_minutes' => $shift ? ($shift->standard_hours_minutes ?? 480) : 480,
            'actual_start_time' => $inTimestamp,
            'actual_end_time' => $outTimestamp,
            'gross_duration_minutes' => $grossMinutes,
            'unpaid_break_minutes' => $unpaidBreakMinutes,
            'net_worked_minutes' => $netMinutes,
            'regular_minutes' => $regularMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'is_overnight' => $isOvernight,
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }
}