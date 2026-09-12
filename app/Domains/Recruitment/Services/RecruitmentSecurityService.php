<?php

namespace App\Domains\Recruitment\Services;

use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentInterviewEvaluation;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class RecruitmentSecurityService
{
    public function authorizeRequisitionAccess(User $user, HcmRecruitmentRequisition $requisition): void
    {
        // 1. Multi-tenant isolation
        if ((string) $user->tenant_id !== (string) $requisition->tenant_id) {
            throw new AuthorizationException('Cross-tenant recruitment requisition access prohibited.');
        }

        // 2. Confidential Requisition gating
        if ($requisition->is_confidential) {
            $isAuthorized = $user->is_platform_admin
                || (string) $user->id === (string) $requisition->recruiter_id
                || (method_exists($user, 'hasPermission') && $user->hasPermission('hcm.requisition.approve'));

            if (!$isAuthorized) {
                throw new AuthorizationException('Access denied: Confidential requisition restricted to authorized recruiters and approvers.');
            }
        }
    }

    public function authorizeCandidateAccess(User $user, HcmRecruitmentCandidate $candidate): void
    {
        if ((string) $user->tenant_id !== (string) $candidate->tenant_id) {
            throw new AuthorizationException('Cross-tenant candidate access prohibited.');
        }
    }

    public function sanitizeScorecardForCandidate(HcmRecruitmentInterviewEvaluation $evaluation): array
    {
        // Candidate must NEVER see confidential notes or internal recruiter commentary
        return [
            'overall_score' => (float) $evaluation->overall_score,
            'recommendation' => $evaluation->recommendation,
            'submitted_at' => $evaluation->submitted_at->toIso8601String(),
        ];
    }
}
