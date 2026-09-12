<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\HcmOvertimeTierRecord;
use Carbon\Carbon;
use Illuminate\Support\Str;

class OvertimeTierCalculationService
{
    /**
     * Calculate multi-tier overtime for a session or daily worked time.
     * Standard rules:
     * - Tier 1 (1.5x): First 120 minutes (2h) of overtime
     * - Tier 2 (2.0x): Next 120 minutes (2h) of overtime
     * - Tier 3 (2.5x/3.0x): Overtime > 4h, OR all overtime on Rest Day / Holiday
     */
    public function calculateDailyOvertimeTiers(
        string $tenantId,
        string $employeeId,
        Carbon $overtimeDate,
        int $workedMinutes,
        int $standardDailyMinutes = 480,
        bool $isRestDay = false,
        bool $isHoliday = false,
        ?AttendanceSession $session = null,
        bool $isPreApproved = false
    ): HcmOvertimeTierRecord {
        $category = 'daily_overtime';
        if ($isHoliday) {
            $category = 'holiday_overtime';
        } elseif ($isRestDay) {
            $category = 'rest_day_overtime';
        }

        $tier1 = 0;
        $tier2 = 0;
        $tier3 = 0;

        if ($isHoliday || $isRestDay) {
            // On rest day or holiday, all worked time is Tier 3 premium overtime
            $tier3 = $workedMinutes;
        } else {
            $totalOvertime = max(0, $workedMinutes - $standardDailyMinutes);

            if ($totalOvertime > 0) {
                // Tier 1: up to 120m
                $tier1 = min($totalOvertime, 120);

                // Tier 2: between 121 and 240m
                $remainingAfterTier1 = max(0, $totalOvertime - 120);
                $tier2 = min($remainingAfterTier1, 120);

                // Tier 3: remaining above 240m
                $tier3 = max(0, $remainingAfterTier1 - 120);
            }
        }

        $totalOt = $tier1 + $tier2 + $tier3;
        $isUnauthorized = ($totalOt > 0 && ! $isPreApproved);

        return HcmOvertimeTierRecord::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'employee_id' => $employeeId,
            'attendance_session_id' => $session?->id,
            'overtime_date' => $overtimeDate->toDateString(),
            'tier_1_minutes' => $tier1,
            'tier_2_minutes' => $tier2,
            'tier_3_minutes' => $tier3,
            'total_overtime_minutes' => $totalOt,
            'overtime_category' => $category,
            'is_unauthorized' => $isUnauthorized,
            'status' => $totalOt > 0 ? ($isPreApproved ? 'approved' : 'pending_approval') : 'calculated',
            'approved_at' => $isPreApproved ? now() : null,
        ]);
    }

    /**
     * Approve an overtime tier record.
     */
    public function approveOvertime(string $recordId, int $approverId): HcmOvertimeTierRecord
    {
        $record = HcmOvertimeTierRecord::findOrFail($recordId);

        $record->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'is_unauthorized' => false,
        ]);

        return $record;
    }

    /**
     * Reject an overtime tier record.
     */
    public function rejectOvertime(string $recordId, int $approverId, string $reason): HcmOvertimeTierRecord
    {
        $record = HcmOvertimeTierRecord::findOrFail($recordId);

        $record->update([
            'status' => 'rejected',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $record;
    }
}