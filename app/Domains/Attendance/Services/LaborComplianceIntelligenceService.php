<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\HcmLaborComplianceCheck;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LaborComplianceIntelligenceService
{
    /**
     * Evaluate labor compliance rules for a completed session.
     * Rules checked:
     * - MAX_DAILY_HOURS (threshold e.g. 720m = 12h)
     * - MIN_REST_PERIOD (threshold e.g. 660m = 11h rest between consecutive shifts)
     * - MANDATORY_MEAL_BREAK (if worked > 360m = 6h, requires >= 30m break)
     */
    public function evaluateSessionCompliance(
        AttendanceSession $session,
        int $maxDailyMinutes = 720,
        int $minRestMinutes = 660,
        int $minMealBreakMinutes = 30
    ): Collection {
        $checks = collect();

        // 1. Max daily hours
        if ($session->net_worked_minutes > $maxDailyMinutes) {
            $checks->push(HcmLaborComplianceCheck::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $session->tenant_id,
                'employee_id' => $session->employee_id,
                'check_date' => $session->session_date,
                'attendance_session_id' => $session->id,
                'rule_code' => 'MAX_DAILY_HOURS',
                'severity' => $session->net_worked_minutes > ($maxDailyMinutes + 120) ? 'breach' : 'critical',
                'threshold_value' => $maxDailyMinutes,
                'actual_value' => $session->net_worked_minutes,
                'status' => 'flagged',
            ]));
        }

        // 2. Minimum rest period from previous session
        if ($session->actual_start_time) {
            $prevSession = AttendanceSession::where('tenant_id', $session->tenant_id)
                ->where('employee_id', $session->employee_id)
                ->where('id', '!=', $session->id)
                ->whereNotNull('actual_end_time')
                ->where('actual_end_time', '<=', $session->actual_start_time)
                ->orderByDesc('actual_end_time')
                ->first();

            if ($prevSession) {
                $restMinutes = $prevSession->actual_end_time->diffInMinutes($session->actual_start_time);
                if ($restMinutes < $minRestMinutes) {
                    $checks->push(HcmLaborComplianceCheck::create([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $session->tenant_id,
                        'employee_id' => $session->employee_id,
                        'check_date' => $session->session_date,
                        'attendance_session_id' => $session->id,
                        'rule_code' => 'MIN_REST_PERIOD',
                        'severity' => $restMinutes < 480 ? 'breach' : 'warning',
                        'threshold_value' => $minRestMinutes,
                        'actual_value' => (int) $restMinutes,
                        'status' => 'flagged',
                    ]));
                }
            }
        }

        // 3. Mandatory meal break for shifts > 6h (360m)
        if ($session->gross_duration_minutes > 360) {
            $breakMinutes = ($session->total_break_minutes ?? 0) + ($session->unpaid_break_minutes ?? 0);
            if ($breakMinutes < $minMealBreakMinutes) {
                $checks->push(HcmLaborComplianceCheck::create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $session->tenant_id,
                    'employee_id' => $session->employee_id,
                    'check_date' => $session->session_date,
                    'attendance_session_id' => $session->id,
                    'rule_code' => 'MANDATORY_MEAL_BREAK',
                    'severity' => 'warning',
                    'threshold_value' => $minMealBreakMinutes,
                    'actual_value' => $breakMinutes,
                    'status' => 'flagged',
                ]));
            }
        }

        return $checks;
    }

    /**
     * Waive a compliance check with documented reason.
     */
    public function waiveComplianceCheck(string $checkId, int $reviewerId, string $reason): HcmLaborComplianceCheck
    {
        $check = HcmLaborComplianceCheck::findOrFail($checkId);

        $check->update([
            'status' => 'waived',
            'waiver_reason' => $reason,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ]);

        return $check;
    }
}