<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Models\Timesheet;
use App\Domains\Employee\Models\Employee;
use Illuminate\Support\Collection;

class AttendanceExportService
{
    /**
     * Generate standard Payroll Integration contract payload for approved timesheets in a period.
     *
     * @return array<int, array{
     *     employee_id: string,
     *     employee_number: string,
     *     employee_name: string,
     *     period_start: string,
     *     period_end: string,
     *     regular_minutes: int,
     *     regular_hours: float,
     *     approved_overtime_minutes: int,
     *     approved_overtime_hours: float,
     *     absence_minutes: int,
     *     absence_hours: float,
     *     late_minutes: int,
     *     unpaid_break_minutes: int,
     *     holiday_minutes: int
     * }>
     */
    public function generatePayrollExportPayload(string $tenantId, string $startDate, string $endDate): array
    {
        $timesheets = Timesheet::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('start_date', '<=', $startDate)
            ->whereDate('end_date', '>=', $endDate)
            ->whereIn('status', ['hr_approved', 'locked', 'exported'])
            ->with('employee')
            ->get();

        $payrollData = [];
        foreach ($timesheets as $ts) {
            $emp = $ts->employee;
            if (! $emp) {
                continue;
            }

            $payrollData[] = [
                'employee_id' => $emp->id,
                'employee_number' => $emp->employee_number ?? $emp->employee_code,
                'employee_name' => $emp->fullName(),
                'period_start' => $ts->start_date->toDateString(),
                'period_end' => $ts->end_date->toDateString(),
                'regular_minutes' => $ts->total_regular_minutes,
                'regular_hours' => round($ts->total_regular_minutes / 60, 2),
                'approved_overtime_minutes' => $ts->total_approved_overtime_minutes,
                'approved_overtime_hours' => round($ts->total_approved_overtime_minutes / 60, 2),
                'absence_minutes' => $ts->total_absence_minutes,
                'absence_hours' => round($ts->total_absence_minutes / 60, 2),
                'late_minutes' => $ts->total_late_minutes,
                'unpaid_break_minutes' => $ts->total_unpaid_leave_minutes,
                'holiday_minutes' => $ts->total_holiday_minutes,
            ];
        }

        return $payrollData;
    }

    public function generateCsv(array $data): string
    {
        if (empty($data)) {
            return "Employee Code,Employee Name,Start Date,End Date,Regular Hours,Approved OT Hours,Absence Hours,Late Minutes\n";
        }

        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, array_keys($data[0]));

        foreach ($data as $row) {
            fputcsv($fp, $row);
        }

        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return $csv ?: '';
    }
}
