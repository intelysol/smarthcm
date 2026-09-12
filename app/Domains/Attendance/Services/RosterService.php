<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Enums\RosterStatus;
use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class RosterService
{
    public function __construct(
        protected RosterConflictEngine $conflictEngine
    ) {}

    public function createPeriod(string $tenantId, array $data, ?User $actor = null): RosterPeriod
    {
        return RosterPeriod::query()->create([
            'tenant_id' => $tenantId,
            'company_id' => $data['company_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'name' => $data['name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => RosterStatus::DRAFT->value,
            'created_by' => $actor?->id,
        ]);
    }

    public function assignShift(
        RosterPeriod $period,
        Employee $employee,
        string $date,
        ?ShiftDefinition $shift = null,
        ?User $actor = null,
        ?string $notes = null
    ): RosterAssignment {
        $tenantId = $period->tenant_id;

        // Conflict check
        $conflicts = $this->conflictEngine->detectConflicts($employee, $date, $shift);
        $blockingConflict = collect($conflicts)->firstWhere('severity', 'blocking');

        if ($blockingConflict) {
            throw ValidationException::withMessages([
                'roster_assignment' => [$blockingConflict['message']],
            ]);
        }

        /** @var RosterAssignment $assignment */
        $assignment = RosterAssignment::query()->updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'employee_id' => $employee->id,
                'roster_date' => $date,
            ],
            [
                'roster_period_id' => $period->id,
                'shift_definition_id' => $shift?->id,
                'location_id' => $employee->work_location_id,
                'department_id' => $employee->department_id,
                'assignment_status' => $shift ? 'scheduled' : 'off_day',
                'is_published' => $period->status === RosterStatus::PUBLISHED->value,
                'notes' => $notes,
                'created_by' => $actor?->id,
                'updated_by' => $actor?->id,
            ]
        );

        if (! empty($conflicts)) {
            $this->conflictEngine->recordConflicts($assignment, $conflicts);
        }

        return $assignment;
    }

    public function bulkAssign(RosterPeriod $period, array $assignmentsList, ?User $actor = null): Collection
    {
        $created = collect();
        foreach ($assignmentsList as $item) {
            $employee = Employee::query()->where('tenant_id', $period->tenant_id)->findOrFail($item['employee_id']);
            $shift = ! empty($item['shift_definition_id'])
                ? ShiftDefinition::query()->where('tenant_id', $period->tenant_id)->find($item['shift_definition_id'])
                : null;

            $created->push($this->assignShift($period, $employee, $item['date'], $shift, $actor, $item['notes'] ?? null));
        }

        return $created;
    }

    public function swapShifts(RosterAssignment $assignmentA, RosterAssignment $assignmentB, ?User $actor = null): void
    {
        if ($assignmentA->tenant_id !== $assignmentB->tenant_id) {
            throw ValidationException::withMessages(['swap' => ['Cannot swap assignments across different tenants.']]);
        }

        $shiftA = $assignmentA->shift_definition_id;
        $shiftB = $assignmentB->shift_definition_id;

        $assignmentA->update([
            'shift_definition_id' => $shiftB,
            'assignment_status' => 'swapped',
            'updated_by' => $actor?->id,
        ]);

        $assignmentB->update([
            'shift_definition_id' => $shiftA,
            'assignment_status' => 'swapped',
            'updated_by' => $actor?->id,
        ]);
    }

    public function replaceEmployee(RosterAssignment $assignment, Employee $replacement, ?User $actor = null): RosterAssignment
    {
        $assignment->update([
            'replacement_employee_id' => $replacement->id,
            'assignment_status' => 'replaced',
            'updated_by' => $actor?->id,
        ]);

        return $assignment;
    }

    public function publishRoster(RosterPeriod $period, User $publisher): RosterPeriod
    {
        $period->update([
            'status' => RosterStatus::PUBLISHED->value,
            'published_at' => now(),
            'published_by' => $publisher->id,
        ]);

        $period->assignments()->update(['is_published' => true]);

        return $period;
    }

    public function lockRoster(RosterPeriod $period): RosterPeriod
    {
        $period->update(['status' => RosterStatus::LOCKED->value]);
        return $period;
    }
}
