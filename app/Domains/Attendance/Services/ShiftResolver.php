<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Models\Holiday;
use App\Domains\Attendance\Models\HolidayCalendar;
use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Models\ShiftPattern;
use App\Domains\Attendance\Models\WorkCalendar;
use App\Domains\Employee\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class ShiftResolver
{
    /**
     * Resolve the planned shift definition for an employee on a given date.
     *
     * Priority Hierarchy:
     * 1. Direct Roster Assignment (Highest)
     * 2. Shift Pattern rotation cycle
     * 3. Employee direct shift assignment
     * 4. Work Calendar default shift / working day
     *
     * @return array{
     *     shift: ShiftDefinition|null,
     *     is_working_day: bool,
     *     is_holiday: bool,
     *     holiday: Holiday|null,
     *     source: string,
     *     roster_assignment: RosterAssignment|null
     * }
     */
    public function resolvePlannedShift(Employee $employee, string|Carbon $date): array
    {
        $carbonDate = CarbonImmutable::parse($date);
        $dateStr = $carbonDate->toDateString();
        $tenantId = $employee->tenant_id;

        // Check Holidays
        $holiday = $this->resolveHoliday($employee, $carbonDate);
        $isHoliday = $holiday !== null;

        // 1. Direct Roster Assignment
        $rosterAssignment = RosterAssignment::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->whereDate('roster_date', $dateStr)
            ->with('shift.breaks')
            ->first();

        if ($rosterAssignment) {
            if ($rosterAssignment->assignment_status === 'off_day' || ! $rosterAssignment->shift_definition_id) {
                return [
                    'shift' => null,
                    'is_working_day' => false,
                    'is_holiday' => $isHoliday,
                    'holiday' => $holiday,
                    'source' => 'roster_off_day',
                    'roster_assignment' => $rosterAssignment,
                ];
            }

            return [
                'shift' => $rosterAssignment->shift,
                'is_working_day' => true,
                'is_holiday' => $isHoliday,
                'holiday' => $holiday,
                'source' => 'roster_assignment',
                'roster_assignment' => $rosterAssignment,
            ];
        }

        // 2. Shift Pattern check
        $pattern = ShiftPattern::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with('patternDays.shift.breaks')
            ->first();

        if ($pattern && $pattern->patternDays->isNotEmpty()) {
            $cycleLength = max(1, $pattern->cycle_length_days);
            // Day sequence based on reference start or day of year
            $daySeq = ($carbonDate->dayOfWeekIso - 1) % $cycleLength + 1;
            $patternDay = $pattern->patternDays->firstWhere('day_sequence', $daySeq);

            if ($patternDay) {
                if ($patternDay->is_off_day || ! $patternDay->shift_definition_id) {
                    return [
                        'shift' => null,
                        'is_working_day' => false,
                        'is_holiday' => $isHoliday,
                        'holiday' => $holiday,
                        'source' => 'shift_pattern_off_day',
                        'roster_assignment' => null,
                    ];
                }

                return [
                    'shift' => $patternDay->shift,
                    'is_working_day' => true,
                    'is_holiday' => $isHoliday,
                    'holiday' => $holiday,
                    'source' => 'shift_pattern',
                    'roster_assignment' => null,
                ];
            }
        }

        // 3. Employee direct shift
        if ($employee->shift_id) {
            $shift = ShiftDefinition::query()
                ->where('tenant_id', $tenantId)
                ->where('id', $employee->shift_id)
                ->where('is_active', true)
                ->with('breaks')
                ->first();

            if ($shift) {
                $isWorking = $this->isCalendarWorkingDay($employee, $carbonDate);
                return [
                    'shift' => $isWorking ? $shift : null,
                    'is_working_day' => $isWorking,
                    'is_holiday' => $isHoliday,
                    'holiday' => $holiday,
                    'source' => 'employee_default_shift',
                    'roster_assignment' => null,
                ];
            }
        }

        // 4. Work Calendar default
        $isWorking = $this->isCalendarWorkingDay($employee, $carbonDate);
        $defaultShift = ShiftDefinition::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with('breaks')
            ->first();

        return [
            'shift' => $isWorking ? $defaultShift : null,
            'is_working_day' => $isWorking,
            'is_holiday' => $isHoliday,
            'holiday' => $holiday,
            'source' => 'work_calendar_default',
            'roster_assignment' => null,
        ];
    }

    public function isCalendarWorkingDay(Employee $employee, CarbonImmutable $date): bool
    {
        $tenantId = $employee->tenant_id;
        $calendar = WorkCalendar::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($employee) {
                $q->where('company_id', $employee->company_id)
                    ->orWhere('is_default', true);
            })
            ->with(['days', 'exceptions'])
            ->first();

        if (! $calendar) {
            // Default: Mon..Fri are working (1..5)
            return in_array($date->dayOfWeekIso, [1, 2, 3, 4, 5], true);
        }

        // Check exception date first
        $exception = $calendar->exceptions->first(function ($ex) use ($date) {
            return CarbonImmutable::parse($ex->exception_date)->isSameDay($date);
        });
        if ($exception) {
            return $exception->is_working_day;
        }

        // Check regular weekday rule
        $dayRule = $calendar->days->firstWhere('day_of_week', $date->dayOfWeekIso);
        if ($dayRule) {
            return $dayRule->is_working_day;
        }

        return in_array($date->dayOfWeekIso, [1, 2, 3, 4, 5], true);
    }

    public function resolveHoliday(Employee $employee, CarbonImmutable $date): ?Holiday
    {
        $calendar = HolidayCalendar::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where(function ($q) use ($employee) {
                $q->where('company_id', $employee->company_id)
                    ->orWhere('is_default', true);
            })
            ->with('holidays')
            ->first();

        if (! $calendar) {
            return null;
        }

        return $calendar->holidays->first(function (Holiday $h) use ($date) {
            return CarbonImmutable::parse($h->holiday_date)->isSameDay($date);
        });
    }
}
