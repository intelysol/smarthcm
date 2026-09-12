<?php

namespace App\Domains\EmployeeRelations\Services;

use App\Domains\EmployeeRelations\Enums\AppealStatus;
use App\Domains\EmployeeRelations\Enums\CaseStatus;
use App\Domains\EmployeeRelations\Events\EmployeeRelationAppealResolved;
use App\Domains\EmployeeRelations\Events\EmployeeRelationAppealSubmitted;
use App\Domains\EmployeeRelations\Events\EmployeeRelationCorrectiveActionCompleted;
use App\Domains\EmployeeRelations\Events\EmployeeRelationCorrectiveActionCreated;
use App\Domains\EmployeeRelations\Events\EmployeeRelationDecisionRecorded;
use App\Domains\EmployeeRelations\Events\EmployeeRelationHearingScheduled;
use App\Domains\EmployeeRelations\Models\EmployeeRelationAppeal;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCorrectiveAction;
use App\Domains\EmployeeRelations\Models\EmployeeRelationDecision;
use App\Domains\EmployeeRelations\Models\EmployeeRelationHearing;
use App\Domains\EmployeeRelations\Models\EmployeeRelationHearingOutcome;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DecisionAndAppealService
{
    /**
     * Schedule a formal hearing
     */
    public function scheduleHearing(EmployeeRelationCase $case, User $chairperson, array $data): EmployeeRelationHearing
    {
        $hearing = EmployeeRelationHearing::query()->create([
            'tenant_id' => $case->tenant_id,
            'case_id' => $case->id,
            'title' => $data['title'] ?? 'Formal ER Hearing',
            'scheduled_at' => $data['scheduled_at'],
            'location' => $data['location'] ?? 'HR Conference Room / Virtual',
            'chairperson_id' => $chairperson->id,
            'status' => 'scheduled',
            'notes' => $data['notes'] ?? null,
        ]);

        event(new EmployeeRelationHearingScheduled($case, $hearing));

        return $hearing;
    }

    /**
     * Record outcome of a hearing
     */
    public function recordHearingOutcome(EmployeeRelationHearing $hearing, User $enteredBy, array $data): EmployeeRelationHearingOutcome
    {
        return DB::transaction(function () use ($hearing, $enteredBy, $data) {
            $hearing->update(['status' => 'completed']);

            return EmployeeRelationHearingOutcome::query()->create([
                'tenant_id' => $hearing->tenant_id,
                'hearing_id' => $hearing->id,
                'case_id' => $hearing->case_id,
                'entered_by' => $enteredBy->id,
                'summary' => $data['summary'],
                'recommendations' => $data['recommendations'] ?? null,
                'entered_at' => now(),
            ]);
        });
    }

    /**
     * Record formal case decision (Human decision maker)
     */
    public function recordDecision(EmployeeRelationCase $case, User $decisionMaker, array $data): EmployeeRelationDecision
    {
        return DB::transaction(function () use ($case, $decisionMaker, $data) {
            $decision = EmployeeRelationDecision::query()->create([
                'tenant_id' => $case->tenant_id,
                'case_id' => $case->id,
                'decision_maker_id' => $decisionMaker->id,
                'decision' => $data['decision'],
                'reason' => $data['reason'],
                'effective_date' => $data['effective_date'] ?? now()->toDateString(),
                'decided_at' => now(),
                'is_draft' => $data['is_draft'] ?? false,
            ]);

            // If not a draft, move case to DECISION or ACTION status
            if (! $decision->is_draft) {
                $hasActions = ! empty($data['actions']);
                $newStatus = $hasActions ? CaseStatus::ACTION->value : CaseStatus::DECISION->value;
                $case->update(['status' => $newStatus]);

                // Create corrective actions if provided
                if ($hasActions) {
                    foreach ($data['actions'] as $act) {
                        $this->createCorrectiveAction($case, $decision, $act, $decisionMaker);
                    }
                }

                event(new EmployeeRelationDecisionRecorded($case, $decision));
            }

            return $decision->fresh('correctiveActions');
        });
    }

    /**
     * Create corrective action
     */
    public function createCorrectiveAction(EmployeeRelationCase $case, ?EmployeeRelationDecision $decision, array $data, ?User $actor = null): EmployeeRelationCorrectiveAction
    {
        $action = EmployeeRelationCorrectiveAction::query()->create([
            'tenant_id' => $case->tenant_id,
            'case_id' => $case->id,
            'decision_id' => $decision?->id,
            'action_type' => $data['action_type'],
            'description' => $data['description'],
            'assigned_to_employee_id' => $data['assigned_to_employee_id'] ?? $case->subject_employee_id,
            'supervisor_id' => $data['supervisor_id'] ?? null,
            'start_date' => $data['start_date'] ?? now()->toDateString(),
            'due_date' => $data['due_date'],
            'status' => 'assigned',
            'employee_acknowledgement_status' => 'pending',
        ]);

        event(new EmployeeRelationCorrectiveActionCreated($case, $action));

        return $action;
    }

    /**
     * Employee acknowledges corrective action
     */
    public function acknowledgeAction(EmployeeRelationCorrectiveAction $action, string $status, ?string $comment = null): EmployeeRelationCorrectiveAction
    {
        $action->update([
            'employee_acknowledgement_status' => $status, // acknowledged, commented, declined
            'employee_acknowledged_at' => now(),
            'employee_comment' => $comment,
            'status' => 'in_progress',
        ]);

        return $action->fresh();
    }

    /**
     * Complete corrective action
     */
    public function completeAction(EmployeeRelationCorrectiveAction $action): EmployeeRelationCorrectiveAction
    {
        $action->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        event(new EmployeeRelationCorrectiveActionCompleted($action->case, $action));

        return $action->fresh();
    }

    /**
     * Verify corrective action by HR/Supervisor
     */
    public function verifyAction(EmployeeRelationCorrectiveAction $action, User $verifier): EmployeeRelationCorrectiveAction
    {
        $action->update([
            'status' => 'verified',
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);

        return $action->fresh();
    }

    /**
     * Submit an appeal
     */
    public function submitAppeal(EmployeeRelationCase $case, User $appellant, array $data): EmployeeRelationAppeal
    {
        return DB::transaction(function () use ($case, $appellant, $data) {
            $appealCount = $case->appeals()->count() + 1;
            $appealNumber = "APP-{$case->case_number}-{$appealCount}";

            $appeal = EmployeeRelationAppeal::query()->create([
                'tenant_id' => $case->tenant_id,
                'case_id' => $case->id,
                'appeal_number' => $appealNumber,
                'submitted_by' => $appellant->id,
                'reason' => $data['reason'],
                'grounds' => $data['grounds'] ?? null,
                'submitted_at' => now(),
                'status' => AppealStatus::SUBMITTED->value,
            ]);

            $case->update(['status' => CaseStatus::APPEAL->value]);

            event(new EmployeeRelationAppealSubmitted($case, $appeal));

            return $appeal;
        });
    }

    /**
     * Resolve an appeal (Independent reviewer enforcement)
     */
    public function resolveAppeal(EmployeeRelationAppeal $appeal, User $reviewer, array $data): EmployeeRelationAppeal
    {
        return DB::transaction(function () use ($appeal, $reviewer, $data) {
            $case = $appeal->case;

            // Enforce Appeal Independence (section 55)
            $isConflict = $case->assignments()
                ->where('user_id', $reviewer->id)
                ->whereIn('role', ['investigator', 'decision_maker'])
                ->exists();

            if ($isConflict && ! $reviewer->is_super_admin) {
                throw ValidationException::withMessages([
                    'reviewer_id' => 'Appeal reviewer cannot be the original investigator or decision maker.',
                ]);
            }

            $appeal->update([
                'reviewer_id' => $reviewer->id,
                'status' => AppealStatus::RESOLVED->value,
                'decision' => $data['decision'], // upheld, partially_upheld, overturned, rejected, withdrawn, modified
                'decision_reason' => $data['decision_reason'],
                'resolved_at' => now(),
                'resolved_by' => $reviewer->id,
            ]);

            // Update case status accordingly
            if (in_array($data['decision'], ['upheld', 'overturned'], true)) {
                $case->update(['status' => CaseStatus::REVIEW->value]);
            } else {
                $case->update(['status' => CaseStatus::CLOSED->value]);
            }

            event(new EmployeeRelationAppealResolved($case, $appeal));

            return $appeal->fresh();
        });
    }
}
