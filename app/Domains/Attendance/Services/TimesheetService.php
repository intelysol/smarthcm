<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Enums\TimesheetStatus;
use App\Domains\Attendance\Models\AttendancePeriod;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\Timesheet;
use App\Domains\Attendance\Models\TimesheetEntry;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;

class TimesheetService
{
    /**
     * Generate or recalculate an itemized timesheet for an employee over a date range.
     */
    public function generateTimesheet(
        Employee $employee,
        string $startDate,
        string $endDate,
        string $periodType = 'monthly',
        ?User $actor = null
    ): Timesheet {
        $tenantId = $employee->tenant_id;
        $start = CarbonImmutable::parse($startDate);
        $end = CarbonImmutable::parse($endDate);

        // Match AttendancePeriod if exists
        $attendancePeriod = AttendancePeriod::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('start_date', '<=', $startDate)
            ->whereDate('end_date', '>=', $endDate)
            ->first();

        // Fetch existing sessions in range
        $sessions = AttendanceSession::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->whereDate('session_date', '>=', $startDate)
            ->whereDate('session_date', '<=', $endDate)
            ->with('shift')
            ->get()
            ->keyBy(fn ($s) => CarbonImmutable::parse($s->session_date)->toDateString());

        $totalScheduled = 0;
        $totalWorked = 0;
        $totalRegular = 0;
        $totalOvertime = 0;
        $totalApprovedOvertime = 0;
        $totalLate = 0;
        $totalEarly = 0;
        $totalAbsence = 0;
        $totalHoliday = 0;

        /** @var Timesheet $timesheet */
        $timesheet = Timesheet::query()->updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'employee_id' => $employee->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            [
                'attendance_period_id' => $attendancePeriod?->id,
                'period_type' => $periodType,
                'status' => TimesheetStatus::DRAFT->value,
                'created_by' => $actor?->id,
                'updated_by' => $actor?->id,
            ]
        );

        $timesheet->entries()->delete();

        $dates = CarbonPeriod::create($start, $end);
        foreach ($dates as $d) {
            $dStr = $d->toDateString();
            $session = $sessions->get($dStr);

            $scheduledMins = $session ? $session->scheduled_minutes : 0;
            $workedMins = $session ? $session->net_worked_minutes : 0;
            $regMins = $session ? $session->regular_minutes : 0;
            $otMins = $session ? $session->overtime_minutes : 0;
            $apprOtMins = $session ? $session->approved_overtime_minutes : 0;
            $lateMins = $session ? $session->late_minutes : 0;
            $earlyMins = $session ? $session->early_departure_minutes : 0;
            $absenceMins = ($session && $session->status === 'absent') ? $scheduledMins : 0;
            $isHoliday = $session && $session->status === 'holiday';

            $totalScheduled += $scheduledMins;
            $totalWorked += $workedMins;
            $totalRegular += $regMins;
            $totalOvertime += $otMins;
            $totalApprovedOvertime += $apprOtMins;
            $totalLate += $lateMins;
            $totalEarly += $earlyMins;
            $totalAbsence += $absenceMins;
            if ($isHoliday) {
                $totalHoliday += $scheduledMins;
            }

            TimesheetEntry::query()->create([
                'tenant_id' => $tenantId,
                'timesheet_id' => $timesheet->id,
                'attendance_session_id' => $session?->id,
                'entry_date' => $dStr,
                'shift_code' => $session?->shift?->shift_code,
                'scheduled_minutes' => $scheduledMins,
                'worked_minutes' => $workedMins,
                'regular_minutes' => $regMins,
                'overtime_minutes' => $otMins,
                'approved_overtime_minutes' => $apprOtMins,
                'late_minutes' => $lateMins,
                'early_minutes' => $earlyMins,
                'absence_minutes' => $absenceMins,
                'is_holiday' => $isHoliday,
                'is_weekend' => in_array($d->dayOfWeekIso, [6, 7], true),
                'is_leave' => $session && $session->status === 'on_leave',
                'status' => $session?->status ?? 'rest_day',
            ]);
        }

        $timesheet->update([
            'total_scheduled_minutes' => $totalScheduled,
            'total_worked_minutes' => $totalWorked,
            'total_regular_minutes' => $totalRegular,
            'total_overtime_minutes' => $totalOvertime,
            'total_approved_overtime_minutes' => $totalApprovedOvertime,
            'total_late_minutes' => $totalLate,
            'total_early_departure_minutes' => $totalEarly,
            'total_absence_minutes' => $totalAbsence,
            'total_holiday_minutes' => $totalHoliday,
        ]);

        return $timesheet;
    }

    public function submitTimesheet(Timesheet $timesheet): Timesheet
    {
        $timesheet->update([
            'status' => TimesheetStatus::SUBMITTED->value,
            'submitted_at' => now(),
        ]);

        return $timesheet;
    }

    public function approveTimesheet(Timesheet $timesheet, User $approver): Timesheet
    {
        $timesheet->update([
            'status' => TimesheetStatus::HR_APPROVED->value,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return $timesheet;
    }

    public function rejectTimesheet(Timesheet $timesheet, User $approver, string $reason): Timesheet
    {
        $timesheet->update([
            'status' => TimesheetStatus::REJECTED->value,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return $timesheet;
    }
}
