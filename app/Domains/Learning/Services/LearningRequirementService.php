<?php

namespace App\Domains\Learning\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Events\Services\EventBus;
use App\Domains\Learning\Events\LearningRequirementAssigned;
use App\Domains\Learning\Events\LearningRequirementOverdue;
use App\Domains\Learning\Events\LearningRequirementWaived;
use App\Domains\Learning\Models\LearningRequirement;
use App\Domains\Learning\Models\LearningRequirementAssignment;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LearningRequirementService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly EventBus $events
    ) {}

    /**
     * Re-evaluate and assign mandatory learning requirements for an employee.
     *
     * @return list<LearningRequirementAssignment>
     */
    public function evaluateRequirementsForEmployee(Employee $employee, string $trigger = 'hired'): array
    {
        $requirements = LearningRequirement::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('status', 'active')
            ->get();

        $assigned = [];

        foreach ($requirements as $requirement) {
            if ($this->matchesTarget($requirement, $employee)) {
                $assignment = $this->assignRequirement($requirement, $employee);
                if ($assignment) {
                    $assigned[] = $assignment;
                }
            }
        }

        return $assigned;
    }

    public function assignRequirement(LearningRequirement $requirement, Employee $employee): ?LearningRequirementAssignment
    {
        $existing = LearningRequirementAssignment::query()
            ->where('requirement_id', $requirement->id)
            ->where('employee_id', $employee->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Calculate due date
        $dueDate = match ($requirement->deadline_type) {
            'relative_hiring' => $employee->joining_date
                ? Carbon::parse($employee->joining_date)->addDays($requirement->days_offset ?? 30)
                : now()->addDays($requirement->days_offset ?? 30),
            'relative_promotion', 'relative_assignment' => now()->addDays($requirement->days_offset ?? 30),
            default => $requirement->due_date ? Carbon::parse($requirement->due_date) : now()->addDays(30),
        };

        return DB::transaction(function () use ($requirement, $employee, $dueDate) {
            $assignment = LearningRequirementAssignment::query()->create([
                'tenant_id' => $employee->tenant_id,
                'requirement_id' => $requirement->id,
                'employee_id' => $employee->id,
                'course_id' => $requirement->course_id,
                'status' => 'assigned',
                'assigned_at' => now(),
                'due_at' => $dueDate,
            ]);

            LearningRequirementAssigned::dispatch($assignment);

            $this->audit->record(
                (string) $employee->tenant_id,
                'LearningRequirementAssigned',
                'assign_requirement',
                LearningRequirementAssignment::class,
                (string) $assignment->id,
                null,
                null,
                [
                    'requirement_id' => $requirement->id,
                    'employee_id' => $employee->id,
                    'due_at' => $dueDate->toIso8601String(),
                ]
            );

            return $assignment;
        });
    }

    public function checkDeadlines(string $tenantId): int
    {
        $overdueAssignments = LearningRequirementAssignment::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['assigned', 'enrolled', 'in_progress'])
            ->where('due_at', '<', now())
            ->get();

        $count = 0;
        foreach ($overdueAssignments as $assignment) {
            $assignment->update(['status' => 'overdue']);
            LearningRequirementOverdue::dispatch($assignment);
            $count++;
        }

        return $count;
    }

    public function waiveRequirement(
        User $actor,
        LearningRequirementAssignment $assignment,
        string $reason
    ): LearningRequirementAssignment {
        return DB::transaction(function () use ($actor, $assignment, $reason) {
            $assignment->update([
                'status' => 'waived',
                'waived_at' => now(),
                'waiver_reason' => $reason,
                'waived_by' => $actor->id,
            ]);

            LearningRequirementWaived::dispatch($assignment);

            $this->audit->record(
                (string) $assignment->tenant_id,
                'LearningRequirementWaived',
                'waive_requirement',
                LearningRequirementAssignment::class,
                (string) $assignment->id,
                $actor->id,
                null,
                ['reason' => $reason]
            );

            return $assignment;
        });
    }

    private function matchesTarget(LearningRequirement $requirement, Employee $employee): bool
    {
        return match ($requirement->target_type) {
            'company' => empty($requirement->target_id) || (string) $employee->company_id === (string) $requirement->target_id,
            'department' => (string) $employee->department_id === (string) $requirement->target_id,
            'individual' => (string) $employee->id === (string) $requirement->target_id,
            'job' => (string) $employee->job_id === (string) $requirement->target_id,
            'job_grade' => (string) $employee->job_grade_id === (string) $requirement->target_id,
            'employment_type' => (string) $employee->employment_type === (string) $requirement->target_id,
            default => true,
        };
    }
}
