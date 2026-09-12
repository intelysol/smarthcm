<?php

namespace App\Domains\Attendance\Services\Scheduling;

use App\Domains\Attendance\Models\HcmScheduleChangeLog;
use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SchedulePublicationService
{
    public function __construct(
        protected ScheduleValidationService $validationService
    ) {}

    /**
     * Publish a roster period.
     * Blocks if critical violations exist unless explicitly forced with an authorized override.
     */
    public function publish(
        RosterPeriod $period,
        User $publisher,
        bool $forceOverride = false,
        ?string $overrideReason = null
    ): RosterPeriod {
        if ($period->is_locked) {
            throw ValidationException::withMessages([
                'schedule' => ['Cannot modify or publish a locked schedule period.'],
            ]);
        }

        $validation = $this->validationService->validatePeriod($period);

        if ($validation['critical_count'] > 0 && ! $forceOverride) {
            throw ValidationException::withMessages([
                'schedule_validation' => [
                    "Cannot publish schedule: {$validation['critical_count']} critical violation(s) detected. Please resolve hard constraints before publishing.",
                ],
            ]);
        }

        $period->update([
            'status' => 'published',
            'version' => max(2, (int) ($period->version ?: 1) + 1),
            'published_at' => now(),
            'published_by' => $publisher->id,
            'validation_status' => $validation['validation_status'],
            'validation_summary' => array_merge($validation, [
                'published_by' => $publisher->id,
                'force_override' => $forceOverride,
                'override_reason' => $overrideReason,
            ]),
        ]);

        // Mark all active scheduled assignments as published
        $period->assignments()
            ->where('assignment_status', 'scheduled')
            ->update(['is_published' => true]);

        return $period->fresh();
    }

    /**
     * Lock schedule period to prevent late unauthorized changes.
     */
    public function lock(RosterPeriod $period, User $locker): RosterPeriod
    {
        $period->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $locker->id,
            'status' => 'locked',
        ]);

        return $period->fresh();
    }

    /**
     * Record a modification to a published schedule assignment in the change log.
     */
    public function recordChange(
        RosterAssignment $assignment,
        ?ShiftDefinition $newShift,
        ?string $newDate,
        string $reason,
        User $changedBy
    ): HcmScheduleChangeLog {
        $changeLog = HcmScheduleChangeLog::query()->create([
            'tenant_id' => $assignment->tenant_id,
            'roster_assignment_id' => $assignment->id,
            'previous_shift_id' => $assignment->shift_definition_id,
            'new_shift_id' => $newShift?->id ?? $assignment->shift_definition_id,
            'previous_date' => $assignment->roster_date,
            'new_date' => $newDate ?? $assignment->roster_date,
            'reason' => $reason,
            'changed_by' => $changedBy->id,
        ]);

        $assignment->update([
            'shift_definition_id' => $newShift?->id ?? $assignment->shift_definition_id,
            'roster_date' => $newDate ?? $assignment->roster_date,
            'updated_by' => $changedBy->id,
        ]);

        return $changeLog;
    }
}
