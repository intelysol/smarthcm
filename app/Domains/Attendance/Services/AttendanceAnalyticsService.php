<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Analytics\Services\AnalyticsService;
use App\Domains\Attendance\Models\AttendanceException;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\RosterAssignment;
use Carbon\Carbon;

class AttendanceAnalyticsService
{
    public function __construct(
        protected ?AnalyticsService $analyticsService = null
    ) {}

    /**
     * Compute comprehensive attendance and workforce analytics KPIs.
     */
    public function generateMetrics(string $tenantId, ?string $from = null, ?string $to = null): array
    {
        $fromDate = $from ? Carbon::parse($from)->toDateString() : now()->subMonth()->toDateString();
        $toDate = $to ? Carbon::parse($to)->toDateString() : now()->toDateString();

        $sessionsQuery = AttendanceSession::query()
            ->where('tenant_id', $tenantId)
            ->whereBetween('session_date', [$fromDate, $toDate]);

        $totalSessions = $sessionsQuery->count();
        $presentCount = (clone $sessionsQuery)->whereIn('status', ['present', 'late', 'early_departure', 'overtime', 'half_day'])->count();
        $absentCount = (clone $sessionsQuery)->where('status', 'absent')->count();
        $lateCount = (clone $sessionsQuery)->where('late_minutes', '>', 0)->count();
        $earlyDepartureCount = (clone $sessionsQuery)->where('early_departure_minutes', '>', 0)->count();
        $overtimeCount = (clone $sessionsQuery)->where('overtime_minutes', '>', 0)->count();
        $incompleteCount = (clone $sessionsQuery)->where('status', 'incomplete')->count();

        $totalWorkedMinutes = (clone $sessionsQuery)->sum('net_worked_minutes');
        $totalOvertimeMinutes = (clone $sessionsQuery)->sum('approved_overtime_minutes');

        $punctualityRate = $presentCount > 0 ? round((($presentCount - $lateCount) / $presentCount) * 100, 1) : 100.0;
        $absenteeismRate = $totalSessions > 0 ? round(($absentCount / $totalSessions) * 100, 1) : 0.0;
        $overtimeHours = round($totalOvertimeMinutes / 60, 1);
        $workedHours = round($totalWorkedMinutes / 60, 1);

        // Open Exceptions
        $openExceptions = AttendanceException::query()
            ->where('tenant_id', $tenantId)
            ->whereBetween('exception_date', [$fromDate, $toDate])
            ->whereIn('status', ['open', 'under_review', 'pending_manager'])
            ->count();

        // Breakdown by Shift
        $byShift = (clone $sessionsQuery)
            ->whereNotNull('shift_definition_id')
            ->join('shift_definitions', 'shift_definitions.id', '=', 'attendance_sessions.shift_definition_id')
            ->selectRaw('shift_definitions.name as shift_name, count(*) as count')
            ->groupBy('shift_definitions.name')
            ->pluck('count', 'shift_name')
            ->all();

        // Breakdown by Status
        $byStatus = (clone $sessionsQuery)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        return [
            'total_sessions' => $totalSessions,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'late_count' => $lateCount,
            'early_departure_count' => $earlyDepartureCount,
            'overtime_count' => $overtimeCount,
            'incomplete_count' => $incompleteCount,
            'open_exceptions_count' => $openExceptions,
            'punctuality_rate' => $punctualityRate,
            'absenteeism_rate' => $absenteeismRate,
            'total_worked_hours' => $workedHours,
            'total_overtime_hours' => $overtimeHours,
            'by_shift' => $byShift,
            'by_status' => $byStatus,
        ];
    }
}
