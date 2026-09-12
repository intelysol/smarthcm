<?php

namespace App\Domains\Lifecycle\Services;

use App\Domains\Lifecycle\Enums\PersonnelActionStatus;
use App\Domains\Lifecycle\Models\PersonnelActionChange;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;

class PersonnelActionConflictService
{
    public function detectConflicts(PersonnelActionRequest $request): array
    {
        $conflicts = [];

        // 1. Check for other active/scheduled actions on the same effective date for the same employee
        $overlapping = PersonnelActionRequest::where('tenant_id', $request->tenant_id)
            ->where('employee_id', $request->employee_id)
            ->where('id', '!=', $request->id)
            ->where('effective_date', $request->effective_date)
            ->whereIn('status', [
                PersonnelActionStatus::SUBMITTED->value,
                PersonnelActionStatus::PENDING_APPROVAL->value,
                PersonnelActionStatus::UNDER_REVIEW->value,
                PersonnelActionStatus::APPROVED->value,
                PersonnelActionStatus::SCHEDULED->value,
            ])
            ->first();

        if ($overlapping) {
            $conflicts[] = "Another active action ({$overlapping->request_number}) exists for this employee with the identical effective date ({$request->effective_date->toDateString()}).";
        }

        // 2. Check position conflicts: If this request targets a position, verify no other action claims the same position on this date
        $positionChange = $request->changes()->where('field_name', 'position_id')->first();
        if ($positionChange && !empty($positionChange->new_value)) {
            $conflictingPositionChange = PersonnelActionChange::where('tenant_id', $request->tenant_id)
                ->where('personnel_action_request_id', '!=', $request->id)
                ->where('field_name', 'position_id')
                ->where('new_value', $positionChange->new_value)
                ->where('effective_date', $request->effective_date)
                ->whereHas('request', function ($q) {
                    $q->whereIn('status', [
                        PersonnelActionStatus::PENDING_APPROVAL->value,
                        PersonnelActionStatus::APPROVED->value,
                        PersonnelActionStatus::SCHEDULED->value,
                    ]);
                })
                ->exists();

            if ($conflictingPositionChange) {
                $conflicts[] = "Target position is already claimed by another pending or scheduled personnel action for the same effective date.";
            }
        }

        return $conflicts;
    }
}
