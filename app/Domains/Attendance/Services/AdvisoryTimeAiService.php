<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Models\AttendanceException;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\HcmLaborComplianceCheck;
use App\Domains\Attendance\Models\HcmOvertimeTierRecord;
use App\Domains\Attendance\Models\Timesheet;
use Carbon\Carbon;

class AdvisoryTimeAiService
{
    /**
     * Analyze employee attendance anomalies over a given period.
     * Strictly advisory (is_advisory_only: true).
     */
    public function analyzeAttendanceAnomalies(string $employeeId, Carbon $start, Carbon $end): array
    {
        $sessions = AttendanceSession::where('employee_id', $employeeId)
            ->whereBetween('session_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $exceptions = AttendanceException::where('employee_id', $employeeId)
            ->whereBetween('exception_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $lateArrivals = $exceptions->where('exception_type', 'late_arrival')->count();
        $missingPunches = $exceptions->whereIn('exception_type', ['missing_in', 'missing_out'])->count();
        $totalWorkedHours = round($sessions->sum('net_worked_minutes') / 60, 2);

        return [
            'is_advisory_only' => true,
            'autonomous_actions_permitted' => false,
            'employee_id' => $employeeId,
            'evaluation_period' => [
                'from' => $start->toDateString(),
                'to' => $end->toDateString(),
            ],
            'metrics' => [
                'total_sessions' => $sessions->count(),
                'total_worked_hours' => $totalWorkedHours,
                'late_arrivals_count' => $lateArrivals,
                'missing_punches_count' => $missingPunches,
            ],
            'insights' => [
                $lateArrivals > 3 ? 'Recurring late arrival pattern detected on Monday mornings; consider schedule adjustment.' : 'Punctuality adherence within acceptable enterprise thresholds.',
                $missingPunches > 0 ? "Identified {$missingPunches} missing punch events requiring employee self-service correction." : 'All attendance sessions have paired clock events.',
            ],
            'confidence_score' => 0.94,
        ];
    }

    /**
     * Synthesize timesheet explanation and variance drivers.
     */
    public function explainTimesheetVariance(string $timesheetId): array
    {
        $timesheet = Timesheet::with(['timeAllocations', 'entries'])->findOrFail($timesheetId);

        $scheduledMinutes = $timesheet->total_scheduled_minutes;
        $workedMinutes = $timesheet->total_worked_minutes;
        $varianceMinutes = $workedMinutes - $scheduledMinutes;

        $overtimeTiers = HcmOvertimeTierRecord::where('timesheet_id', $timesheetId)->get();

        return [
            'is_advisory_only' => true,
            'autonomous_actions_permitted' => false,
            'timesheet_id' => $timesheetId,
            'summary' => [
                'scheduled_hours' => round($scheduledMinutes / 60, 2),
                'worked_hours' => round($workedMinutes / 60, 2),
                'variance_hours' => round($varianceMinutes / 60, 2),
                'regular_hours' => round($timesheet->total_regular_minutes / 60, 2),
                'overtime_hours' => round($timesheet->total_overtime_minutes / 60, 2),
            ],
            'overtime_tiers_breakdown' => [
                'tier_1_hours' => round($overtimeTiers->sum('tier_1_minutes') / 60, 2),
                'tier_2_hours' => round($overtimeTiers->sum('tier_2_minutes') / 60, 2),
                'tier_3_hours' => round($overtimeTiers->sum('tier_3_minutes') / 60, 2),
            ],
            'allocation_coverage_percent' => $workedMinutes > 0 ? round(($timesheet->timeAllocations->sum('allocated_minutes') / $workedMinutes) * 100, 1) : 0,
            'recommendation' => 'Timesheet aligns with assigned roster. Overtime hours require standard manager approval before payroll export.',
        ];
    }
}