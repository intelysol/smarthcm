<?php

namespace App\Domains\EmployeeRelations\Services;

use App\Domains\EmployeeRelations\Enums\ConfidentialityLevel;
use App\Domains\EmployeeRelations\Enums\NoteVisibility;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseNote;
use App\Domains\EmployeeRelations\Models\EmployeeRelationEvidence;
use App\Domains\EmployeeRelations\Models\EmployeeRelationInterviewNote;
use App\Domains\EmployeeRelations\Models\EmployeeRelationStatement;
use App\Domains\Platform\Services\AuthorizationService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class CaseAuthorizationService
{
    public function __construct(
        protected AuthorizationService $platformAuth
    ) {}

    /**
     * Enforces tenant boundary. Cross-tenant access is unconditionally denied.
     */
    protected function checkTenant(User $user, EmployeeRelationCase $case): bool
    {
        return ! empty($user->tenant_id) && $user->tenant_id === $case->tenant_id;
    }

    /**
     * Check whether the user is explicitly assigned to this case in any active role.
     */
    public function isAssigned(User $user, EmployeeRelationCase $case, ?array $roles = null): bool
    {
        if (! $this->checkTenant($user, $case)) {
            return false;
        }

        $query = $case->assignments()
            ->where('user_id', $user->id)
            ->where('status', 'active');

        if ($roles !== null) {
            $query->whereIn('role', $roles);
        }

        return $query->exists();
    }

    /**
     * Check if user is a direct participant on the case.
     */
    public function isParticipant(User $user, EmployeeRelationCase $case, ?array $participantTypes = null): bool
    {
        if (! $this->checkTenant($user, $case)) {
            return false;
        }

        $query = $case->participants()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if (! empty($user->employee?->id)) {
                    $q->orWhere('employee_id', $user->employee->id);
                }
            });

        if ($participantTypes !== null) {
            $query->whereIn('participant_type', $participantTypes);
        }

        return $query->exists();
    }

    /**
     * Determine if a user can view the case.
     */
    public function canViewCase(User $user, EmployeeRelationCase $case): bool
    {
        if (! $this->checkTenant($user, $case)) {
            return false;
        }

        // Creator of the case
        if ($case->created_by === $user->id) {
            return true;
        }

        // Check explicit case assignment
        if ($this->isAssigned($user, $case)) {
            return true;
        }

        // Check if user is subject/reporter in employee portal
        if ($this->isParticipant($user, $case, ['subject', 'reporter'])) {
            return true;
        }

        // Platform permission check
        if (! $this->platformAuth->can($user, 'hcm.employee_relations.case.view')) {
            return false;
        }

        // Check confidentiality level permissions
        if ($case->confidentiality_level === ConfidentialityLevel::HIGHLY_CONFIDENTIAL->value) {
            return $this->platformAuth->can($user, 'hcm.employee_relations.confidential.view');
        }

        if (in_array($case->confidentiality_level, [
            ConfidentialityLevel::RESTRICTED->value,
            ConfidentialityLevel::LEGAL_RESTRICTED->value,
        ], true)) {
            return $this->platformAuth->can($user, 'hcm.employee_relations.restricted.view');
        }

        return true;
    }

    /**
     * Determine if a user can edit / manage the case.
     */
    public function canEditCase(User $user, EmployeeRelationCase $case): bool
    {
        if (! $this->checkTenant($user, $case)) {
            return false;
        }

        if (! $this->platformAuth->can($user, 'hcm.employee_relations.case.edit')) {
            return false;
        }

        // Case owners, HR partners, or global ER managers
        if ($this->platformAuth->can($user, 'hcm.employee_relations.manage')) {
            return true;
        }

        return $this->isAssigned($user, $case, ['case_owner', 'hr_partner']);
    }

    /**
     * Determine if user can view case evidence.
     */
    public function canViewEvidence(User $user, EmployeeRelationCase $case, ?EmployeeRelationEvidence $evidence = null): bool
    {
        if (! $this->checkTenant($user, $case)) {
            return false;
        }

        if (! $this->platformAuth->can($user, 'hcm.employee_relations.evidence.view')) {
            return false;
        }

        // Restricted evidence checks
        if ($evidence && in_array($evidence->confidentiality, [
            ConfidentialityLevel::RESTRICTED->value,
            ConfidentialityLevel::LEGAL_RESTRICTED->value,
        ], true)) {
            return $this->platformAuth->can($user, 'hcm.employee_relations.restricted.view')
                || $this->isAssigned($user, $case, ['legal_reviewer', 'investigator']);
        }

        return $this->isAssigned($user, $case) || $this->platformAuth->can($user, 'hcm.employee_relations.manage');
    }

    /**
     * Determine if user can view a participant statement.
     */
    public function canViewStatement(User $user, EmployeeRelationCase $case, ?EmployeeRelationStatement $statement = null): bool
    {
        if (! $this->checkTenant($user, $case)) {
            return false;
        }

        // If statement author is viewing their own statement
        if ($statement && $statement->participant?->user_id === $user->id) {
            return true;
        }

        if (! $this->platformAuth->can($user, 'hcm.employee_relations.statement.view')) {
            return false;
        }

        return $this->isAssigned($user, $case, ['case_owner', 'hr_partner', 'investigator', 'reviewer', 'decision_maker', 'legal_reviewer'])
            || $this->platformAuth->can($user, 'hcm.employee_relations.manage');
    }

    /**
     * Determine if user can view investigation workspace.
     */
    public function canViewInvestigation(User $user, EmployeeRelationCase $case): bool
    {
        if (! $this->checkTenant($user, $case)) {
            return false;
        }

        if (! $this->platformAuth->can($user, 'hcm.employee_relations.investigation.view')) {
            return false;
        }

        return $this->isAssigned($user, $case, ['investigator', 'case_owner', 'hr_partner', 'reviewer', 'legal_reviewer'])
            || $this->platformAuth->can($user, 'hcm.employee_relations.manage');
    }

    /**
     * Determine if user can record formal case decision.
     */
    public function canMakeDecision(User $user, EmployeeRelationCase $case): bool
    {
        if (! $this->checkTenant($user, $case)) {
            return false;
        }

        if (! $this->platformAuth->can($user, 'hcm.employee_relations.decision.manage')) {
            return false;
        }

        return $this->isAssigned($user, $case, ['decision_maker', 'case_owner'])
            || $this->platformAuth->can($user, 'hcm.employee_relations.manage');
    }

    /**
     * Determine if user can review / resolve an appeal.
     */
    public function canManageAppeal(User $user, EmployeeRelationCase $case): bool
    {
        if (! $this->checkTenant($user, $case)) {
            return false;
        }

        if (! $this->platformAuth->can($user, 'hcm.employee_relations.appeal.manage')) {
            return false;
        }

        // Independence rule: Original investigator or decision maker cannot decide appeal
        $wasInvestigatorOrDecisionMaker = $case->assignments()
            ->where('user_id', $user->id)
            ->whereIn('role', ['investigator', 'decision_maker'])
            ->exists();

        if ($wasInvestigatorOrDecisionMaker && ! $user->is_super_admin) {
            return false;
        }

        return true;
    }

    /**
     * Determine if user can export case bundle / summary.
     */
    public function canExportCase(User $user, EmployeeRelationCase $case): bool
    {
        if (! $this->checkTenant($user, $case)) {
            return false;
        }

        return $this->platformAuth->can($user, 'hcm.employee_relations.report.export')
            && $this->canViewCase($user, $case);
    }

    /**
     * Determine if user can view specific case note based on note visibility.
     */
    public function canViewNote(User $user, EmployeeRelationCase $case, EmployeeRelationCaseNote $note): bool
    {
        if (! $this->checkTenant($user, $case)) {
            return false;
        }

        if ($note->author_id === $user->id) {
            return true;
        }

        return match ($note->visibility) {
            NoteVisibility::CASE_TEAM->value => $this->isAssigned($user, $case) || $this->platformAuth->can($user, 'hcm.employee_relations.manage'),
            NoteVisibility::HR_ONLY->value => $this->platformAuth->can($user, 'hcm.employee_relations.manage') || $this->isAssigned($user, $case, ['case_owner', 'hr_partner']),
            NoteVisibility::INVESTIGATOR_ONLY->value => $this->isAssigned($user, $case, ['investigator']),
            NoteVisibility::LEGAL_ONLY->value => $this->platformAuth->can($user, 'hcm.employee_relations.legal.view') || $this->isAssigned($user, $case, ['legal_reviewer']),
            NoteVisibility::DECISION_MAKER->value => $this->isAssigned($user, $case, ['decision_maker', 'reviewer', 'case_owner']),
            default => false,
        };
    }

    /**
     * Determine if user can view interview notes.
     */
    public function canViewInterviewNote(User $user, EmployeeRelationCase $case, EmployeeRelationInterviewNote $note): bool
    {
        if (! $this->checkTenant($user, $case)) {
            return false;
        }

        if ($note->author_id === $user->id) {
            return true;
        }

        // Case subjects / normal employees MUST NEVER see raw interview notes
        if ($this->isParticipant($user, $case, ['subject', 'witness'])) {
            return false;
        }

        return match ($note->visibility) {
            'investigator_only' => $this->isAssigned($user, $case, ['investigator']),
            'hr_only' => $this->platformAuth->can($user, 'hcm.employee_relations.manage') || $this->isAssigned($user, $case, ['case_owner', 'hr_partner']),
            'legal_only' => $this->platformAuth->can($user, 'hcm.employee_relations.legal.view') || $this->isAssigned($user, $case, ['legal_reviewer']),
            'case_team' => $this->isAssigned($user, $case),
            default => false,
        };
    }

    /**
     * Anonymous reporter identification protection.
     */
    public function canViewAnonymousReporter(User $user, EmployeeRelationCase $case): bool
    {
        // Anonymous reporter details are masked by policy unless super admin / explicitly permitted legal review
        return false;
    }

    public function authorizeOrFail(bool $condition, string $message = 'Action unauthorized for this ER case.'): void
    {
        if (! $condition) {
            throw new AuthorizationException($message);
        }
    }
}
