<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Enums\PeriodStatus;
use App\Domains\Attendance\Enums\TimesheetStatus;
use App\Domains\Attendance\Models\AttendancePeriod;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AttendancePeriodService
{
    public function createPeriod(string $tenantId, array $data, ?User $actor = null): AttendancePeriod
    {
        return AttendancePeriod::query()->create([
            'tenant_id' => $tenantId,
            'company_id' => $data['company_id'] ?? null,
            'period_name' => $data['period_name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => PeriodStatus::OPEN->value,
            'created_by' => $actor?->id,
        ]);
    }

    public function lockPeriod(AttendancePeriod $period, User $actor): AttendancePeriod
    {
        if ($period->isLocked()) {
            throw ValidationException::withMessages(['period' => ['Attendance period is already locked.']]);
        }

        $period->update([
            'status' => PeriodStatus::LOCKED->value,
            'locked_at' => now(),
            'locked_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);

        // Lock all contained timesheets
        $period->timesheets()->update(['status' => TimesheetStatus::LOCKED->value]);

        return $period;
    }

    public function reopenPeriod(AttendancePeriod $period, User $actor, string $reason): AttendancePeriod
    {
        if (! $period->isLocked()) {
            throw ValidationException::withMessages(['period' => ['Attendance period is not currently locked.']]);
        }

        $period->update([
            'status' => PeriodStatus::REOPENED->value,
            'reopened_at' => now(),
            'reopened_by' => $actor->id,
            'reopen_reason' => $reason,
            'updated_by' => $actor->id,
        ]);

        return $period;
    }

    public function closePeriod(AttendancePeriod $period, User $actor): AttendancePeriod
    {
        $period->update([
            'status' => PeriodStatus::CLOSED->value,
            'closed_at' => now(),
            'updated_by' => $actor->id,
        ]);

        return $period;
    }
}
