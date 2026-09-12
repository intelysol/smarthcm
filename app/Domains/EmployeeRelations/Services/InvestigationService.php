<?php

namespace App\Domains\EmployeeRelations\Services;

use App\Domains\EmployeeRelations\Enums\CaseStatus;
use App\Domains\EmployeeRelations\Events\EmployeeRelationFindingRecorded;
use App\Domains\EmployeeRelations\Events\EmployeeRelationInterviewCompleted;
use App\Domains\EmployeeRelations\Events\EmployeeRelationInvestigationCompleted;
use App\Domains\EmployeeRelations\Events\EmployeeRelationInvestigationStarted;
use App\Domains\EmployeeRelations\Events\EmployeeRelationStatementSubmitted;
use App\Domains\EmployeeRelations\Models\EmployeeRelationAllegation;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseParticipant;
use App\Domains\EmployeeRelations\Models\EmployeeRelationFinding;
use App\Domains\EmployeeRelations\Models\EmployeeRelationInterview;
use App\Domains\EmployeeRelations\Models\EmployeeRelationInterviewNote;
use App\Domains\EmployeeRelations\Models\EmployeeRelationInvestigation;
use App\Domains\EmployeeRelations\Models\EmployeeRelationInvestigationQuestion;
use App\Domains\EmployeeRelations\Models\EmployeeRelationInvestigationStep;
use App\Domains\EmployeeRelations\Models\EmployeeRelationStatement;
use App\Domains\EmployeeRelations\Models\EmployeeRelationStatementVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvestigationService
{
    /**
     * Start an investigation
     */
    public function startInvestigation(EmployeeRelationCase $case, User $investigator, string $scope, ?string $targetDate = null): EmployeeRelationInvestigation
    {
        return DB::transaction(function () use ($case, $investigator, $scope, $targetDate) {
            $investigation = EmployeeRelationInvestigation::query()->create([
                'tenant_id' => $case->tenant_id,
                'case_id' => $case->id,
                'investigator_id' => $investigator->id,
                'scope' => $scope,
                'start_date' => now()->toDateString(),
                'target_completion_date' => $targetDate ?? now()->addDays(14)->toDateString(),
                'status' => 'active',
            ]);

            // Add standard plan steps
            $defaultSteps = [
                ['step_type' => 'collect_evidence', 'title' => 'Collect Document & System Evidence', 'sort_order' => 1],
                ['step_type' => 'interview_reporter', 'title' => 'Interview Complainant / Reporter', 'sort_order' => 2],
                ['step_type' => 'interview_subject', 'title' => 'Interview Subject Employee', 'sort_order' => 3],
                ['step_type' => 'interview_witness', 'title' => 'Interview Named Witnesses', 'sort_order' => 4],
                ['step_type' => 'review_documents', 'title' => 'Review Evidence & Policies', 'sort_order' => 5],
                ['step_type' => 'prepare_findings', 'title' => 'Prepare Findings Report', 'sort_order' => 6],
            ];

            foreach ($defaultSteps as $step) {
                EmployeeRelationInvestigationStep::query()->create([
                    'tenant_id' => $case->tenant_id,
                    'investigation_id' => $investigation->id,
                    'case_id' => $case->id,
                    'step_type' => $step['step_type'],
                    'title' => $step['title'],
                    'assigned_to' => $investigator->id,
                    'status' => 'pending',
                    'due_date' => now()->addDays(7)->toDateString(),
                    'sort_order' => $step['sort_order'],
                ]);
            }

            // Update case status to investigation if not already
            if (in_array($case->status, [CaseStatus::ASSIGNED->value, CaseStatus::TRIAGE->value], true)) {
                $case->update(['status' => CaseStatus::INVESTIGATION->value]);
            }

            event(new EmployeeRelationInvestigationStarted($case, $investigation));

            return $investigation->load('steps');
        });
    }

    /**
     * Add or update investigation step
     */
    public function updateStep(EmployeeRelationInvestigationStep $step, array $data, User $actor): EmployeeRelationInvestigationStep
    {
        $step->update([
            'status' => $data['status'] ?? $step->status,
            'title' => $data['title'] ?? $step->title,
            'description' => $data['description'] ?? $step->description,
            'assigned_to' => $data['assigned_to'] ?? $step->assigned_to,
            'due_date' => $data['due_date'] ?? $step->due_date,
            'completed_at' => ($data['status'] ?? '') === 'completed' ? now() : $step->completed_at,
        ]);

        return $step->fresh();
    }

    /**
     * Record participant statement with versioning
     */
    public function recordStatement(EmployeeRelationCase $case, EmployeeRelationCaseParticipant $participant, array $data, User $actor): EmployeeRelationStatement
    {
        return DB::transaction(function () use ($case, $participant, $data, $actor) {
            $statement = EmployeeRelationStatement::query()->create([
                'tenant_id' => $case->tenant_id,
                'case_id' => $case->id,
                'participant_id' => $participant->id,
                'statement_type' => $data['statement_type'] ?? 'witness',
                'content' => $data['content'],
                'submitted_at' => now(),
                'verified_at' => ! empty($data['is_verified']) ? now() : null,
                'verified_by' => ! empty($data['is_verified']) ? $actor->id : null,
                'is_confidential' => $data['is_confidential'] ?? true,
                'current_version' => 1,
            ]);

            // Create initial version snapshot
            EmployeeRelationStatementVersion::query()->create([
                'tenant_id' => $case->tenant_id,
                'statement_id' => $statement->id,
                'version_number' => 1,
                'content' => $data['content'],
                'change_reason' => 'Initial statement submission',
                'submitted_by' => $actor->id,
                'created_at' => now(),
            ]);

            event(new EmployeeRelationStatementSubmitted($case, $statement));

            return $statement->load('versions');
        });
    }

    /**
     * Update statement (Adds immutable version record)
     */
    public function updateStatement(EmployeeRelationStatement $statement, string $newContent, string $reason, User $actor): EmployeeRelationStatement
    {
        return DB::transaction(function () use ($statement, $newContent, $reason, $actor) {
            $nextVersion = $statement->current_version + 1;

            EmployeeRelationStatementVersion::query()->create([
                'tenant_id' => $statement->tenant_id,
                'statement_id' => $statement->id,
                'version_number' => $nextVersion,
                'content' => $newContent,
                'change_reason' => $reason,
                'submitted_by' => $actor->id,
                'created_at' => now(),
            ]);

            $statement->update([
                'content' => $newContent,
                'current_version' => $nextVersion,
                'verified_at' => null, // Reset verification on modification
                'verified_by' => null,
            ]);

            return $statement->fresh('versions');
        });
    }

    /**
     * Schedule & manage interview
     */
    public function scheduleInterview(EmployeeRelationCase $case, EmployeeRelationCaseParticipant $participant, User $investigator, array $data): EmployeeRelationInterview
    {
        return EmployeeRelationInterview::query()->create([
            'tenant_id' => $case->tenant_id,
            'case_id' => $case->id,
            'participant_id' => $participant->id,
            'investigator_id' => $investigator->id,
            'scheduled_at' => $data['scheduled_at'],
            'location' => $data['location'] ?? 'Virtual / Meeting Room',
            'format' => $data['format'] ?? 'in_person',
            'status' => 'scheduled',
        ]);
    }

    /**
     * Complete interview & save notes
     */
    public function completeInterview(EmployeeRelationInterview $interview, ?string $notes = null, ?User $actor = null): EmployeeRelationInterview
    {
        return DB::transaction(function () use ($interview, $notes, $actor) {
            $interview->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            if ($notes) {
                EmployeeRelationInterviewNote::query()->create([
                    'tenant_id' => $interview->tenant_id,
                    'interview_id' => $interview->id,
                    'case_id' => $interview->case_id,
                    'author_id' => $actor?->id ?? $interview->investigator_id,
                    'notes' => $notes,
                    'is_confidential' => true,
                    'visibility' => 'investigator_only',
                    'version' => 1,
                ]);
            }

            event(new EmployeeRelationInterviewCompleted($interview->case, $interview));

            return $interview->fresh('notes');
        });
    }

    /**
     * Record finding on allegation
     */
    public function recordFinding(EmployeeRelationCase $case, ?EmployeeRelationAllegation $allegation, User $investigator, array $data): EmployeeRelationFinding
    {
        return DB::transaction(function () use ($case, $allegation, $investigator, $data) {
            $finding = EmployeeRelationFinding::query()->create([
                'tenant_id' => $case->tenant_id,
                'case_id' => $case->id,
                'allegation_id' => $allegation?->id,
                'investigator_id' => $investigator->id,
                'finding' => $data['finding'], // substantiated, partially_substantiated, unsubstantiated, inconclusive, withdrawn
                'confidence' => $data['confidence'] ?? 'high',
                'rationale' => $data['rationale'],
                'evidence_summary' => $data['evidence_summary'] ?? null,
                'recorded_at' => now(),
            ]);

            if ($allegation) {
                $allegation->update(['status' => 'finding_recorded']);
            }

            event(new EmployeeRelationFindingRecorded($case, $finding));

            return $finding;
        });
    }

    /**
     * Complete investigation
     */
    public function completeInvestigation(EmployeeRelationInvestigation $investigation, string $summaryFindings, User $actor): EmployeeRelationInvestigation
    {
        return DB::transaction(function () use ($investigation, $summaryFindings, $actor) {
            $investigation->update([
                'status' => 'completed',
                'completed_at' => now(),
                'summary_findings' => $summaryFindings,
            ]);

            // Transition case to review
            $case = $investigation->case;
            if ($case->status === CaseStatus::INVESTIGATION->value) {
                $case->update(['status' => CaseStatus::REVIEW->value]);
            }

            event(new EmployeeRelationInvestigationCompleted($case, $investigation));

            return $investigation->fresh();
        });
    }
}
