<?php

namespace App\Domains\EmployeeRelations\Services;

use App\Domains\EmployeeRelations\Enums\CasePriority;
use App\Domains\EmployeeRelations\Enums\CaseSeverity;
use App\Domains\EmployeeRelations\Enums\CaseStatus;
use App\Domains\EmployeeRelations\Enums\ConfidentialityLevel;
use App\Domains\EmployeeRelations\Events\EmployeeRelationCaseAssigned;
use App\Domains\EmployeeRelations\Events\EmployeeRelationCaseClosed;
use App\Domains\EmployeeRelations\Events\EmployeeRelationCaseCreated;
use App\Domains\EmployeeRelations\Events\EmployeeRelationCaseSubmitted;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseAssignment;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseNote;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseParticipant;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseReporter;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseSla;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseTask;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseToken;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseTriage;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseType;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmployeeRelationCaseService
{
    /**
     * Generate unique case number for tenant: ER-YYYY-XXXXX
     */
    public function generateCaseNumber(string $tenantId): string
    {
        $year = date('Y');
        $count = EmployeeRelationCase::query()
            ->where('tenant_id', $tenantId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        $sequence = str_pad((string) $count, 5, '0', STR_PAD_LEFT);
        $candidate = "ER-{$year}-{$sequence}";

        // Ensure uniqueness
        while (EmployeeRelationCase::query()->where('tenant_id', $tenantId)->where('case_number', $candidate)->exists()) {
            $count++;
            $sequence = str_pad((string) $count, 5, '0', STR_PAD_LEFT);
            $candidate = "ER-{$year}-{$sequence}";
        }

        return $candidate;
    }

    /**
     * Create case (Standard HR intake)
     */
    public function createCase(string $tenantId, array $data, ?User $actor = null): EmployeeRelationCase
    {
        return DB::transaction(function () use ($tenantId, $data, $actor) {
            $caseType = EmployeeRelationCaseType::query()
                ->where(function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
                })
                ->findOrFail($data['case_type_id']);

            $caseNumber = $this->generateCaseNumber($tenantId);

            $case = EmployeeRelationCase::query()->create([
                'tenant_id' => $tenantId,
                'case_number' => $caseNumber,
                'case_type_id' => $caseType->id,
                'subject_employee_id' => $data['subject_employee_id'] ?? null,
                'subject_type' => $data['subject_type'] ?? 'employee',
                'subject_name' => $data['subject_name'] ?? null,
                'title' => $data['title'],
                'summary' => $data['summary'],
                'priority' => $data['priority'] ?? $caseType->default_priority ?? CasePriority::NORMAL->value,
                'severity' => $data['severity'] ?? $caseType->default_severity ?? CaseSeverity::MODERATE->value,
                'status' => $data['status'] ?? CaseStatus::SUBMITTED->value,
                'confidentiality_level' => $data['confidentiality_level'] ?? $caseType->default_confidentiality ?? ConfidentialityLevel::STANDARD_CONFIDENTIAL->value,
                'incident_date' => $data['incident_date'] ?? null,
                'incident_location' => $data['incident_location'] ?? null,
                'opened_at' => now(),
                'target_resolution_date' => $data['target_resolution_date'] ?? now()->addDays(14)->toDateString(),
                'is_anonymous' => false,
                'created_by' => $actor?->id,
            ]);

            // Create Reporter record
            EmployeeRelationCaseReporter::query()->create([
                'tenant_id' => $tenantId,
                'case_id' => $case->id,
                'reporter_type' => $data['reporter_type'] ?? 'hr',
                'reporter_employee_id' => $data['reporter_employee_id'] ?? ($actor?->employee?->id ?? null),
                'reporter_user_id' => $actor?->id,
                'reporter_name' => $data['reporter_name'] ?? ($actor?->name ?? 'HR Administrator'),
                'reporter_email' => $data['reporter_email'] ?? $actor?->email,
                'reporter_phone' => $data['reporter_phone'] ?? null,
                'is_anonymous' => false,
                'allow_followup' => true,
            ]);

            // Add subject as participant if employee
            if (! empty($case->subject_employee_id)) {
                EmployeeRelationCaseParticipant::query()->create([
                    'tenant_id' => $tenantId,
                    'case_id' => $case->id,
                    'participant_type' => 'subject',
                    'employee_id' => $case->subject_employee_id,
                    'name' => $case->subjectEmployee?->full_name ?? $case->subject_name,
                    'role_title' => 'Subject Employee',
                ]);
            }

            // Create Initial Response & Triage SLAs
            $this->createInitialSlas($case);

            event(new EmployeeRelationCaseCreated($case));
            event(new EmployeeRelationCaseSubmitted($case));

            return $case->fresh(['caseType', 'subjectEmployee', 'reporters', 'slas']);
        });
    }

    /**
     * Submit Self-Service Case by Employee
     */
    public function submitEmployeeReport(string $tenantId, User $user, array $data): EmployeeRelationCase
    {
        return DB::transaction(function () use ($tenantId, $user, $data) {
            $caseType = EmployeeRelationCaseType::query()
                ->where(function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
                })
                ->where('is_self_service_allowed', true)
                ->findOrFail($data['case_type_id']);

            $caseNumber = $this->generateCaseNumber($tenantId);

            $case = EmployeeRelationCase::query()->create([
                'tenant_id' => $tenantId,
                'case_number' => $caseNumber,
                'case_type_id' => $caseType->id,
                'subject_employee_id' => $data['subject_employee_id'] ?? null,
                'subject_type' => $data['subject_type'] ?? 'employee',
                'subject_name' => $data['subject_name'] ?? null,
                'title' => $data['title'],
                'summary' => $data['summary'],
                'priority' => $data['urgency'] ?? $caseType->default_priority ?? CasePriority::NORMAL->value,
                'severity' => $caseType->default_severity ?? CaseSeverity::MODERATE->value,
                'status' => CaseStatus::SUBMITTED->value,
                'confidentiality_level' => $caseType->default_confidentiality ?? ConfidentialityLevel::STANDARD_CONFIDENTIAL->value,
                'incident_date' => $data['incident_date'] ?? null,
                'incident_location' => $data['incident_location'] ?? null,
                'opened_at' => now(),
                'target_resolution_date' => now()->addDays(14)->toDateString(),
                'is_anonymous' => false,
                'created_by' => $user->id,
            ]);

            // Reporter
            EmployeeRelationCaseReporter::query()->create([
                'tenant_id' => $tenantId,
                'case_id' => $case->id,
                'reporter_type' => 'employee',
                'reporter_employee_id' => $user->employee?->id,
                'reporter_user_id' => $user->id,
                'reporter_name' => $user->name,
                'reporter_email' => $user->email,
                'is_anonymous' => false,
                'allow_followup' => true,
            ]);

            // Add reporting employee as reporter participant
            EmployeeRelationCaseParticipant::query()->create([
                'tenant_id' => $tenantId,
                'case_id' => $case->id,
                'participant_type' => 'reporter',
                'employee_id' => $user->employee?->id,
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_title' => 'Complainant / Reporter',
            ]);

            $this->createInitialSlas($case);

            event(new EmployeeRelationCaseCreated($case));
            event(new EmployeeRelationCaseSubmitted($case));

            return $case->fresh(['caseType', 'reporters', 'slas']);
        });
    }

    /**
     * Submit Anonymous Case (No auth required, generates secure token)
     */
    public function submitAnonymousReport(string $tenantId, array $data): array
    {
        return DB::transaction(function () use ($tenantId, $data) {
            $caseType = EmployeeRelationCaseType::query()
                ->where(function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
                })
                ->where('is_anonymous_allowed', true)
                ->findOrFail($data['case_type_id']);

            $caseNumber = $this->generateCaseNumber($tenantId);

            $case = EmployeeRelationCase::query()->create([
                'tenant_id' => $tenantId,
                'case_number' => $caseNumber,
                'case_type_id' => $caseType->id,
                'subject_employee_id' => $data['subject_employee_id'] ?? null,
                'subject_type' => $data['subject_type'] ?? 'department',
                'subject_name' => $data['subject_name'] ?? 'Anonymous Concern',
                'title' => $data['title'],
                'summary' => $data['summary'],
                'priority' => $data['urgency'] ?? $caseType->default_priority ?? CasePriority::HIGH->value,
                'severity' => $caseType->default_severity ?? CaseSeverity::SERIOUS->value,
                'status' => CaseStatus::SUBMITTED->value,
                'confidentiality_level' => ConfidentialityLevel::HIGHLY_CONFIDENTIAL->value,
                'incident_date' => $data['incident_date'] ?? null,
                'incident_location' => $data['incident_location'] ?? null,
                'opened_at' => now(),
                'target_resolution_date' => now()->addDays(21)->toDateString(),
                'is_anonymous' => true,
                'created_by' => null,
            ]);

            // Anonymous reporter record (Zero identifiable information stored)
            EmployeeRelationCaseReporter::query()->create([
                'tenant_id' => $tenantId,
                'case_id' => $case->id,
                'reporter_type' => 'anonymous',
                'reporter_employee_id' => null,
                'reporter_user_id' => null,
                'reporter_name' => 'Anonymous Reporter',
                'reporter_email' => null,
                'reporter_phone' => null,
                'is_anonymous' => true,
                'allow_followup' => true,
            ]);

            // Generate secure random raw token (64 hex characters) and store SHA-256 hash
            $rawToken = 'er_' . bin2hex(random_bytes(24));
            $tokenHash = hash('sha256', $rawToken);

            EmployeeRelationCaseToken::query()->create([
                'tenant_id' => $tenantId,
                'case_id' => $case->id,
                'token_hash' => $tokenHash,
                'expires_at' => now()->addMonths(6),
                'status' => 'active',
            ]);

            $this->createInitialSlas($case);

            event(new EmployeeRelationCaseCreated($case));
            event(new EmployeeRelationCaseSubmitted($case));

            return [
                'case' => $case,
                'token' => $rawToken,
                'case_number' => $caseNumber,
            ];
        });
    }

    /**
     * Resolve anonymous case using raw token
     */
    public function resolveAnonymousToken(string $rawToken): ?EmployeeRelationCase
    {
        $tokenHash = hash('sha256', $rawToken);
        $token = EmployeeRelationCaseToken::query()
            ->where('token_hash', $tokenHash)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if (! $token) {
            return null;
        }

        $token->update(['last_accessed_at' => now()]);

        return $token->case;
    }

    /**
     * Case Triage
     */
    public function triageCase(EmployeeRelationCase $case, array $data, User $triagedBy): EmployeeRelationCaseTriage
    {
        return DB::transaction(function () use ($case, $data, $triagedBy) {
            $triage = EmployeeRelationCaseTriage::query()->updateOrCreate(
                ['case_id' => $case->id],
                [
                    'tenant_id' => $case->tenant_id,
                    'triaged_by' => $triagedBy->id,
                    'recommended_case_type_id' => $data['recommended_case_type_id'] ?? $case->case_type_id,
                    'recommended_priority' => $data['recommended_priority'] ?? $case->priority,
                    'recommended_severity' => $data['recommended_severity'] ?? $case->severity,
                    'requires_investigation' => $data['requires_investigation'] ?? true,
                    'required_investigator_type' => $data['required_investigator_type'] ?? null,
                    'conflict_of_interest_checked' => $data['conflict_of_interest_checked'] ?? true,
                    'escalation_required' => $data['escalation_required'] ?? false,
                    'escalation_reason' => $data['escalation_reason'] ?? null,
                    'triage_notes' => $data['triage_notes'] ?? null,
                    'triaged_at' => now(),
                ]
            );

            // Update case fields
            $case->update([
                'status' => CaseStatus::TRIAGE->value,
                'priority' => $data['recommended_priority'] ?? $case->priority,
                'severity' => $data['recommended_severity'] ?? $case->severity,
                'case_type_id' => $data['recommended_case_type_id'] ?? $case->case_type_id,
            ]);

            // Mark triage SLA as met
            $case->slas()->where('sla_type', 'triage')->where('status', 'pending')->update([
                'status' => 'met',
                'completed_at' => now(),
            ]);

            return $triage;
        });
    }

    /**
     * Assign user to case
     */
    public function assignUser(EmployeeRelationCase $case, User $user, string $role, User $assignedBy, ?string $notes = null): EmployeeRelationCaseAssignment
    {
        return DB::transaction(function () use ($case, $user, $role, $assignedBy, $notes) {
            // Check conflict of interest before investigator assignment
            if ($role === 'investigator') {
                $hasConflict = $case->conflicts()
                    ->where('user_id', $user->id)
                    ->whereIn('declaration', ['conflict_exists', 'potential_conflict'])
                    ->where('status', 'disqualified')
                    ->exists();

                if ($hasConflict) {
                    throw ValidationException::withMessages([
                        'user_id' => 'This user has an active disqualified conflict of interest for this case.',
                    ]);
                }
            }

            $assignment = EmployeeRelationCaseAssignment::query()->create([
                'tenant_id' => $case->tenant_id,
                'case_id' => $case->id,
                'user_id' => $user->id,
                'role' => $role,
                'assigned_by' => $assignedBy->id,
                'assigned_at' => now(),
                'status' => 'active',
                'notes' => $notes,
            ]);

            // If case was in submitted/triage, move to assigned
            if (in_array($case->status, [CaseStatus::SUBMITTED->value, CaseStatus::TRIAGE->value], true)) {
                $case->update(['status' => CaseStatus::ASSIGNED->value]);
            }

            event(new EmployeeRelationCaseAssigned($case, $assignment));

            return $assignment;
        });
    }

    /**
     * Transition case lifecycle state
     */
    public function transitionState(EmployeeRelationCase $case, string $targetState, User $actor, ?string $reason = null): EmployeeRelationCase
    {
        $validTransitions = [
            CaseStatus::DRAFT->value => [CaseStatus::SUBMITTED->value, CaseStatus::CANCELLED->value],
            CaseStatus::SUBMITTED->value => [CaseStatus::TRIAGE->value, CaseStatus::ASSIGNED->value, CaseStatus::REJECTED->value, CaseStatus::WITHDRAWN->value],
            CaseStatus::TRIAGE->value => [CaseStatus::ASSIGNED->value, CaseStatus::INVESTIGATION->value, CaseStatus::REJECTED->value, CaseStatus::WITHDRAWN->value],
            CaseStatus::ASSIGNED->value => [CaseStatus::INVESTIGATION->value, CaseStatus::REVIEW->value, CaseStatus::ON_HOLD->value, CaseStatus::WITHDRAWN->value],
            CaseStatus::INVESTIGATION->value => [CaseStatus::REVIEW->value, CaseStatus::DECISION->value, CaseStatus::ON_HOLD->value, CaseStatus::CANCELLED->value],
            CaseStatus::REVIEW->value => [CaseStatus::DECISION->value, CaseStatus::INVESTIGATION->value, CaseStatus::ACTION->value, CaseStatus::ON_HOLD->value],
            CaseStatus::DECISION->value => [CaseStatus::ACTION->value, CaseStatus::APPEAL->value, CaseStatus::CLOSED->value],
            CaseStatus::ACTION->value => [CaseStatus::APPEAL->value, CaseStatus::CLOSED->value, CaseStatus::ON_HOLD->value],
            CaseStatus::APPEAL->value => [CaseStatus::ACTION->value, CaseStatus::CLOSED->value, CaseStatus::REVIEW->value],
            CaseStatus::ON_HOLD->value => [CaseStatus::ASSIGNED->value, CaseStatus::INVESTIGATION->value, CaseStatus::REVIEW->value, CaseStatus::ACTION->value],
            CaseStatus::CLOSED->value => [CaseStatus::ASSIGNED->value, CaseStatus::ARCHIVED->value],
        ];

        $current = $case->status;
        if (! isset($validTransitions[$current]) || ! in_array($targetState, $validTransitions[$current], true)) {
            // Allow super admin or specific override if authorized
            if (! $actor->is_super_admin) {
                throw ValidationException::withMessages([
                    'status' => "Cannot transition case from {$current} to {$targetState}.",
                ]);
            }
        }

        $case->update([
            'status' => $targetState,
            'closed_at' => $targetState === CaseStatus::CLOSED->value ? now() : null,
            'closed_by' => $targetState === CaseStatus::CLOSED->value ? $actor->id : null,
        ]);

        // Record timeline note
        EmployeeRelationCaseNote::query()->create([
            'tenant_id' => $case->tenant_id,
            'case_id' => $case->id,
            'author_id' => $actor->id,
            'note_type' => 'internal_hr_note',
            'visibility' => 'case_team',
            'content' => "Status transitioned to '{$targetState}' by {$actor->name}." . ($reason ? " Reason: {$reason}" : ''),
        ]);

        if ($targetState === CaseStatus::CLOSED->value) {
            // Mark resolution SLA as met
            $case->slas()->where('sla_type', 'resolution')->where('status', 'pending')->update([
                'status' => 'met',
                'completed_at' => now(),
            ]);
            event(new EmployeeRelationCaseClosed($case));
        }

        return $case->fresh();
    }

    /**
     * Unified Case Timeline
     */
    public function getTimeline(EmployeeRelationCase $case, User $user, CaseAuthorizationService $auth): array
    {
        $timeline = [];

        // 1. Case Created
        $timeline[] = [
            'type' => 'case_created',
            'title' => 'Case Created & Submitted',
            'timestamp' => $case->opened_at->toIso8601String(),
            'description' => "Case {$case->case_number} was logged.",
            'actor' => $case->is_anonymous ? 'Anonymous' : ($case->createdByUser?->name ?? 'System'),
        ];

        // 2. Triage
        if ($case->triage) {
            $timeline[] = [
                'type' => 'triage',
                'title' => 'Case Triaged',
                'timestamp' => $case->triage->triaged_at->toIso8601String(),
                'description' => "Priority: {$case->triage->recommended_priority}, Severity: {$case->triage->recommended_severity}",
                'actor' => $case->triage->triagedByUser?->name ?? 'HR Officer',
            ];
        }

        // 3. Assignments
        foreach ($case->assignments as $asgn) {
            $timeline[] = [
                'type' => 'assignment',
                'title' => "Role Assigned: {$asgn->role}",
                'timestamp' => $asgn->assigned_at->toIso8601String(),
                'description' => "Assigned to {$asgn->user?->name}",
                'actor' => $asgn->assignedByUser?->name ?? 'HR',
            ];
        }

        // 4. Investigations
        if ($auth->canViewInvestigation($user, $case)) {
            foreach ($case->investigations as $inv) {
                $timeline[] = [
                    'type' => 'investigation',
                    'title' => 'Investigation Scope Initiated',
                    'timestamp' => $inv->created_at->toIso8601String(),
                    'description' => "Status: {$inv->status}",
                    'actor' => $inv->investigator?->name ?? 'Lead Investigator',
                ];
            }
        }

        // 5. Interviews
        foreach ($case->interviews as $intv) {
            $timeline[] = [
                'type' => 'interview',
                'title' => "Interview: {$intv->participant?->name}",
                'timestamp' => $intv->scheduled_at->toIso8601String(),
                'description' => "Status: {$intv->status}, Format: {$intv->format}",
                'actor' => $intv->investigator?->name ?? 'Investigator',
            ];
        }

        // 6. Hearings
        foreach ($case->hearings as $hrg) {
            $timeline[] = [
                'type' => 'hearing',
                'title' => "Hearing: {$hrg->title}",
                'timestamp' => $hrg->scheduled_at->toIso8601String(),
                'description' => "Status: {$hrg->status}",
                'actor' => $hrg->chairperson?->name ?? 'Chairperson',
            ];
        }

        // 7. Decisions
        foreach ($case->decisions as $dec) {
            $timeline[] = [
                'type' => 'decision',
                'title' => "Decision: {$dec->decision}",
                'timestamp' => $dec->decided_at->toIso8601String(),
                'description' => $dec->reason,
                'actor' => $dec->decisionMaker?->name ?? 'Decision Maker',
            ];
        }

        // 8. Corrective Actions
        foreach ($case->correctiveActions as $act) {
            $timeline[] = [
                'type' => 'corrective_action',
                'title' => "Action: {$act->action_type}",
                'timestamp' => $act->created_at->toIso8601String(),
                'description' => "Status: {$act->status}, Due: {$act->due_date->toDateString()}",
                'actor' => $act->assignedEmployee?->full_name ?? 'Assigned Employee',
            ];
        }

        // 9. Appeals
        foreach ($case->appeals as $app) {
            $timeline[] = [
                'type' => 'appeal',
                'title' => "Appeal Submitted ({$app->appeal_number})",
                'timestamp' => $app->submitted_at->toIso8601String(),
                'description' => "Status: {$app->status}, Outcome: " . ($app->decision ?? 'Pending'),
                'actor' => $app->submitter?->name ?? 'Appellant',
            ];
        }

        // 10. Closure
        if ($case->closed_at) {
            $timeline[] = [
                'type' => 'case_closed',
                'title' => 'Case Closed',
                'timestamp' => $case->closed_at->toIso8601String(),
                'description' => 'All case investigations, actions, and reviews finalized.',
                'actor' => $case->closedByUser?->name ?? 'HR Administrator',
            ];
        }

        // Sort by timestamp
        usort($timeline, fn ($a, $b) => strcmp($a['timestamp'], $b['timestamp']));

        return $timeline;
    }

    /**
     * Create initial SLAs
     */
    protected function createInitialSlas(EmployeeRelationCase $case): void
    {
        // Initial response: 24h
        EmployeeRelationCaseSla::query()->create([
            'tenant_id' => $case->tenant_id,
            'case_id' => $case->id,
            'sla_type' => 'initial_response',
            'target_hours' => 24,
            'due_at' => now()->addHours(24),
            'status' => 'pending',
        ]);

        // Triage: 48h
        EmployeeRelationCaseSla::query()->create([
            'tenant_id' => $case->tenant_id,
            'case_id' => $case->id,
            'sla_type' => 'triage',
            'target_hours' => 48,
            'due_at' => now()->addHours(48),
            'status' => 'pending',
        ]);

        // Resolution: 336h (14 days)
        EmployeeRelationCaseSla::query()->create([
            'tenant_id' => $case->tenant_id,
            'case_id' => $case->id,
            'sla_type' => 'resolution',
            'target_hours' => 336,
            'due_at' => now()->addDays(14),
            'status' => 'pending',
        ]);
    }
}
