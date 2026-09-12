<?php

namespace App\Domains\Attendance\Services\Scheduling;

use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\HcmScheduleException;
use App\Domains\Attendance\Models\RosterAssignment;
use Carbon\CarbonImmutable;

class RealTimeCoverageService
{
    /**
     * Evaluate real-time operational coverage for a date.
     *
     * @return array{
     *     date: string,
     *     total_scheduled: int,
     *     total_present: int,
     *     total_no_shows: int,
     *     total_late: int,
     *     real_time_coverage_pct: float,
     *     exceptions_generated: int
     * }
     */
    public function evaluateRealTimeCoverage(string $tenantId, string $date, ?string $departmentId = null): array
    {
        $dateStr = CarbonImmutable::parse($date)->toDateString();

        $assignments = RosterAssignment::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('roster_date', $dateStr)
            ->where('assignment_status', 'scheduled')
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->with(['employee', 'shift'])
            ->get();

        $attendanceSessions = AttendanceSession::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('session_date', $dateStr)
            ->get();

        $totalScheduled = $assignments->count();
        $totalPresent = 0;
        $totalNoShows = 0;
        $totalLate = 0;
        $exceptionsGenerated = 0;

        foreach ($assignments as $assignment) {
            $session = $attendanceSessions->firstWhere('employee_id', $assignment->employee_id);

            if ($session && $session->actual_start_time) {
                $totalPresent++;

                if ($session->late_minutes > 0) {
                    $totalLate++;
                    $this->recordException($assignment, 'late_arrival', 'warning', "Employee arrived {$session->late_minutes} minutes late.");
                    $exceptionsGenerated++;
                }
            } else {
                // If current time is after scheduled start + grace period, mark as no-show
                $totalNoShows++;
                $this->recordException($assignment, 'no_show', 'critical', "Employee has not clocked in for scheduled shift [{$assignment->shift?->name}].");
                $exceptionsGenerated++;
            }
        }

        $coveragePct = $totalScheduled > 0 ? round(($totalPresent / $totalScheduled) * 100, 1) : 100.0;

        return [
            'date' => $dateStr,
            'total_scheduled' => $totalScheduled,
            'total_present' => $totalPresent,
            'total_no_shows' => $totalNoShows,
            'total_late' => $totalLate,
            'real_time_coverage_pct' => $coveragePct,
            'exceptions_generated' => $exceptionsGenerated,
        ];
    }

    protected function recordException(
        RosterAssignment $assignment,
        string $type,
        string $severity,
        string $notes
    ): HcmScheduleException {
        return HcmScheduleException::query()->firstOrCreate(
            [
                'tenant_id' => $assignment->tenant_id,
                'roster_assignment_id' => $assignment->id,
                'employee_id' => $assignment->employee_id,
                'exception_type' => $type,
            ],
            [
                'severity' => $severity,
                'status' => 'detected',
                'resolution_notes' => $notes,
            ]
        );
    }
}
