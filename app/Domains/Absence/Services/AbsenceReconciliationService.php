<?php

namespace App\Domains\Absence\Services;

use App\Domains\Absence\Models\HcmAbsenceEvent;
use App\Domains\Absence\Models\HcmAbsenceReconciliation;
use App\Domains\Attendance\Models\AttendanceSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AbsenceReconciliationService
{
    /**
     * Perform multi-way absence reconciliation:
     * - Leave application vs Absence event
     * - Attendance punch during approved full-day leave
     * - Absence without leave application
     */
    public function reconcilePeriod(
        string $tenantId,
        Carbon $start,
        Carbon $end,
        ?int $reconciledById = null
    ): HcmAbsenceReconciliation {
        $discrepancies = [];
        $recordsChecked = 0;

        // 1. Check for Attendance Punches during Approved Leave
        $approvedLeaves = DB::table('leave_applications')
            ->where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->get();

        $recordsChecked += $approvedLeaves->count();

        foreach ($approvedLeaves as $leave) {
            $punchSession = AttendanceSession::where('tenant_id', $tenantId)
                ->where('employee_id', $leave->employee_id)
                ->whereDate('session_date', '>=', $leave->start_date)
                ->whereDate('session_date', '<=', $leave->end_date)
                ->whereNotNull('actual_start_time')
                ->first();

            if ($punchSession) {
                $discrepancies[] = [
                    'employee_id' => $leave->employee_id,
                    'type' => 'attendance_during_approved_leave',
                    'date' => $punchSession->session_date->toDateString(),
                    'detail' => "Employee clocked in on {$punchSession->session_date->toDateString()} despite approved leave application.",
                ];
            }
        }

        // 2. Check for Unplanned Absences without Leave Application
        $unplannedEvents = HcmAbsenceEvent::where('tenant_id', $tenantId)
            ->where('is_planned', false)
            ->whereNull('leave_application_id')
            ->whereBetween('absence_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $recordsChecked += $unplannedEvents->count();

        foreach ($unplannedEvents as $event) {
            $discrepancies[] = [
                'employee_id' => $event->employee_id,
                'type' => 'absence_without_leave_application',
                'date' => $event->absence_date->toDateString(),
                'detail' => "Unplanned absence on {$event->absence_date->toDateString()} has no associated leave application.",
            ];
        }

        $hasDiscrepancies = count($discrepancies) > 0;

        return HcmAbsenceReconciliation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'reconciliation_date' => now()->toDateString(),
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'status' => $hasDiscrepancies ? 'discrepancy_detected' : 'balanced',
            'total_records_checked' => $recordsChecked,
            'discrepant_records_count' => count($discrepancies),
            'discrepancies' => $discrepancies,
            'reconciled_by' => $reconciledById,
            'reconciled_at' => now(),
        ]);
    }
}