<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Models\AttendanceEvent;
use App\Domains\Attendance\Models\AttendancePolicy;
use App\Domains\Attendance\Models\ShiftDefinition;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AttendanceSessionBuilder
{
    /**
     * Build calculated session metrics from normalized events for a planned shift on a date.
     *
     * @param Collection<int, AttendanceEvent> $events
     * @return array{
     *     actual_start_time: string|null,
     *     actual_end_time: string|null,
     *     gross_duration_minutes: int,
     *     total_break_minutes: int,
     *     unpaid_break_minutes: int,
     *     paid_break_minutes: int,
     *     net_worked_minutes: int,
     *     regular_minutes: int,
     *     overtime_minutes: int,
     *     late_minutes: int,
     *     early_departure_minutes: int,
     *     undertime_minutes: int,
     *     status: string,
     *     breaks: array<int, array{start: string, end: string|null, duration: int, is_paid: bool}>
     * }
     */
    public function buildSession(
        Collection $events,
        ?ShiftDefinition $shift,
        AttendancePolicy $policy,
        string $date,
        bool $isWorkingDay = true,
        bool $isHoliday = false
    ): array {
        // Sort events chronologically
        $sortedEvents = $events->sortBy('event_timestamp')->values();

        if ($sortedEvents->isEmpty()) {
            $status = $isHoliday
                ? AttendanceStatus::HOLIDAY->value
                : ($isWorkingDay ? AttendanceStatus::ABSENT->value : AttendanceStatus::REST_DAY->value);

            return [
                'actual_start_time' => null,
                'actual_end_time' => null,
                'gross_duration_minutes' => 0,
                'total_break_minutes' => 0,
                'unpaid_break_minutes' => 0,
                'paid_break_minutes' => 0,
                'net_worked_minutes' => 0,
                'regular_minutes' => 0,
                'overtime_minutes' => 0,
                'late_minutes' => 0,
                'early_departure_minutes' => 0,
                'undertime_minutes' => $isWorkingDay && $shift ? $shift->duration_minutes : 0,
                'status' => $status,
                'breaks' => [],
            ];
        }

        // Identify In and Out events
        $firstEvent = $sortedEvents->first();
        $lastEvent = $sortedEvents->last();

        $actualStart = CarbonImmutable::parse($firstEvent->event_timestamp);
        $actualEnd = ($sortedEvents->count() > 1) ? CarbonImmutable::parse($lastEvent->event_timestamp) : null;

        // Check single punch (Missing Punch)
        if ($sortedEvents->count() === 1 || ! $actualEnd || $actualStart->equalTo($actualEnd)) {
            return [
                'actual_start_time' => $actualStart->toDateTimeString(),
                'actual_end_time' => null,
                'gross_duration_minutes' => 0,
                'total_break_minutes' => 0,
                'unpaid_break_minutes' => 0,
                'paid_break_minutes' => 0,
                'net_worked_minutes' => 0,
                'regular_minutes' => 0,
                'overtime_minutes' => 0,
                'late_minutes' => 0,
                'early_departure_minutes' => 0,
                'undertime_minutes' => $isWorkingDay && $shift ? $shift->duration_minutes : 0,
                'status' => AttendanceStatus::INCOMPLETE->value,
                'breaks' => [],
            ];
        }

        // Multi-punch duty calculation & break deduction
        $breaks = [];
        $totalBreakMinutes = 0;
        $unpaidBreakMinutes = 0;
        $paidBreakMinutes = 0;

        // Extract duty spans (pairs of IN and OUT, or sequential events)
        $inPunches = $sortedEvents->whereIn('event_type', ['check_in', 'break_end'])->values();
        $outPunches = $sortedEvents->whereIn('event_type', ['check_out', 'break_start'])->values();

        // Calculate break intervals between duty blocks
        for ($i = 0; $i < $sortedEvents->count() - 1; $i++) {
            $curr = $sortedEvents[$i];
            $next = $sortedEvents[$i + 1];

            if (in_array($curr->event_type, ['check_out', 'break_start'], true) && in_array($next->event_type, ['check_in', 'break_end'], true)) {
                $bStart = CarbonImmutable::parse($curr->event_timestamp);
                $bEnd = CarbonImmutable::parse($next->event_timestamp);
                $bDuration = max(0, (int) $bStart->diffInMinutes($bEnd));

                $isPaid = false;
                $breaks[] = [
                    'start' => $bStart->toDateTimeString(),
                    'end' => $bEnd->toDateTimeString(),
                    'duration' => $bDuration,
                    'is_paid' => $isPaid,
                ];

                $totalBreakMinutes += $bDuration;
                if (! $isPaid) {
                    $unpaidBreakMinutes += $bDuration;
                } else {
                    $paidBreakMinutes += $bDuration;
                }
            }
        }

        // Gross duration
        $grossDuration = max(0, (int) $actualStart->diffInMinutes($actualEnd));

        // Automatic break deduction if configured and no manual break logged
        if ($policy->auto_deduct_breaks && $totalBreakMinutes === 0 && $shift && $shift->breaks->isNotEmpty()) {
            foreach ($shift->breaks as $sb) {
                if ($sb->is_automatic_deduction && $grossDuration >= 300) { // e.g. after 5 hours
                    $totalBreakMinutes += $sb->duration_minutes;
                    if ($sb->break_type === 'unpaid') {
                        $unpaidBreakMinutes += $sb->duration_minutes;
                    } else {
                        $paidBreakMinutes += $sb->duration_minutes;
                    }
                }
            }
        }

        $netWorkedMinutes = max(0, $grossDuration - $unpaidBreakMinutes);

        // Apply Rounding Rule
        $rounding = max(1, $policy->rounding_interval_minutes);
        if ($rounding > 1) {
            $netWorkedMinutes = match ($policy->rounding_method) {
                'floor' => (int) (floor($netWorkedMinutes / $rounding) * $rounding),
                'ceil' => (int) (ceil($netWorkedMinutes / $rounding) * $rounding),
                default => (int) (round($netWorkedMinutes / $rounding) * $rounding),
            };
        }

        // Scheduled Shift Comparison
        $lateMinutes = 0;
        $earlyDepartureMinutes = 0;
        $undertimeMinutes = 0;
        $regularMinutes = $netWorkedMinutes;
        $overtimeMinutes = 0;

        if ($shift) {
            $scheduledStart = CarbonImmutable::parse("{$date} {$shift->start_time}");
            $scheduledEnd = CarbonImmutable::parse("{$date} {$shift->end_time}");

            // Overnight Shift adjustment
            if ($shift->is_night_shift || $shift->end_time < $shift->start_time) {
                $scheduledEnd = $scheduledEnd->addDay();
            }

            $grace = $shift->grace_period_minutes ?: $policy->grace_period_minutes;

            // Late calculation
            if (! $shift->is_flexible) {
                $rawLate = (int) $scheduledStart->diffInMinutes($actualStart, false);
                if ($rawLate > $grace) {
                    $lateMinutes = $rawLate;
                }

                // Early departure calculation
                $rawEarly = (int) $actualEnd->diffInMinutes($scheduledEnd, false);
                if ($rawEarly > $shift->early_departure_threshold_minutes) {
                    $earlyDepartureMinutes = $rawEarly;
                }
            }

            // Overtime vs Undertime
            $scheduledDuration = $shift->duration_minutes ?: 480;
            if ($netWorkedMinutes > $scheduledDuration) {
                $potentialOt = $netWorkedMinutes - $scheduledDuration;
                if ($shift->overtime_eligible && $potentialOt >= $shift->min_overtime_threshold_minutes) {
                    $overtimeMinutes = $potentialOt;
                    $regularMinutes = $scheduledDuration;
                } else {
                    $regularMinutes = $netWorkedMinutes;
                }
            } elseif ($netWorkedMinutes < $scheduledDuration) {
                $undertimeMinutes = $scheduledDuration - $netWorkedMinutes;
                $regularMinutes = $netWorkedMinutes;
            }
        }

        // Determine Status
        $status = AttendanceStatus::PRESENT->value;
        if ($lateMinutes > 0 && $earlyDepartureMinutes > 0) {
            $status = AttendanceStatus::LATE->value;
        } elseif ($lateMinutes > 0) {
            $status = AttendanceStatus::LATE->value;
        } elseif ($earlyDepartureMinutes > 0) {
            $status = AttendanceStatus::EARLY_DEPARTURE->value;
        } elseif ($overtimeMinutes > 0) {
            $status = AttendanceStatus::OVERTIME->value;
        }

        // Half day check
        if ($policy->half_day_late_threshold_minutes > 0 && $lateMinutes >= $policy->half_day_late_threshold_minutes) {
            $status = AttendanceStatus::HALF_DAY->value;
        }

        return [
            'actual_start_time' => $actualStart->toDateTimeString(),
            'actual_end_time' => $actualEnd->toDateTimeString(),
            'gross_duration_minutes' => $grossDuration,
            'total_break_minutes' => $totalBreakMinutes,
            'unpaid_break_minutes' => $unpaidBreakMinutes,
            'paid_break_minutes' => $paidBreakMinutes,
            'net_worked_minutes' => $netWorkedMinutes,
            'regular_minutes' => $regularMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'late_minutes' => $lateMinutes,
            'early_departure_minutes' => $earlyDepartureMinutes,
            'undertime_minutes' => $undertimeMinutes,
            'status' => $status,
            'breaks' => $breaks,
        ];
    }
}
