<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Models\HcmOvertimeTierRecord;
use App\Domains\Attendance\Models\HcmPayrollTimeExport;
use App\Domains\Attendance\Models\Timesheet;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PayrollTimeExportService
{
    /**
     * Generate a batched payroll export payload for approved timesheets in a period.
     */
    public function generatePayrollExport(
        string $tenantId,
        Carbon $periodStart,
        Carbon $periodEnd,
        ?string $attendancePeriodId = null,
        ?int $exportedById = null
    ): HcmPayrollTimeExport {
        $timesheets = Timesheet::where('tenant_id', $tenantId)
            ->whereDate('start_date', '>=', $periodStart->toDateString())
            ->whereDate('end_date', '<=', $periodEnd->toDateString())
            ->where(function ($q) {
                $q->whereIn('status', ['approved', 'locked', 'hr_approved', 'manager_approved']);
            })
            ->with(['employee'])
            ->get();

        $employeePayloads = [];
        $totalRegularHours = 0.00;
        $totalOtHours = 0.00;
        $totalLeaveHours = 0.00;

        foreach ($timesheets as $ts) {
            $regHours = round($ts->total_regular_minutes / 60, 2);
            $otHours = round($ts->total_overtime_minutes / 60, 2);
            $leaveHours = round(($ts->total_paid_leave_minutes + $ts->total_holiday_minutes) / 60, 2);

            // Fetch tiers if present
            $tiers = HcmOvertimeTierRecord::where('timesheet_id', $ts->id)
                ->where('status', 'approved')
                ->get();

            $tier1Hours = round($tiers->sum('tier_1_minutes') / 60, 2);
            $tier2Hours = round($tiers->sum('tier_2_minutes') / 60, 2);
            $tier3Hours = round($tiers->sum('tier_3_minutes') / 60, 2);

            $employeePayloads[] = [
                'employee_id' => $ts->employee_id,
                'employee_code' => $ts->employee?->employee_number ?? 'N/A',
                'timesheet_id' => $ts->id,
                'regular_hours' => $regHours,
                'total_overtime_hours' => $otHours,
                'overtime_tier_1_hours' => $tier1Hours,
                'overtime_tier_2_hours' => $tier2Hours,
                'overtime_tier_3_hours' => $tier3Hours,
                'leave_hours' => $leaveHours,
            ];

            $totalRegularHours += $regHours;
            $totalOtHours += $otHours;
            $totalLeaveHours += $leaveHours;
        }

        $reference = 'PAY-EXP-' . $periodStart->format('Ymd') . '-' . Str::upper(Str::random(6));

        $export = HcmPayrollTimeExport::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'export_reference' => $reference,
            'attendance_period_id' => $attendancePeriodId,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'total_employees' => count($employeePayloads),
            'total_regular_hours' => $totalRegularHours,
            'total_overtime_hours' => $totalOtHours,
            'total_leave_hours' => $totalLeaveHours,
            'export_payload' => [
                'exported_at' => now()->toIso8601String(),
                'records' => $employeePayloads,
            ],
            'status' => 'generated',
            'exported_by' => $exportedById,
            'exported_at' => now(),
        ]);

        return $export;
    }

    /**
     * Dispatch payload to Integration Hub.
     */
    public function dispatchToIntegrationHub(string $exportId): HcmPayrollTimeExport
    {
        $export = HcmPayrollTimeExport::findOrFail($exportId);
        $export->update(['status' => 'dispatched']);

        return $export;
    }
}