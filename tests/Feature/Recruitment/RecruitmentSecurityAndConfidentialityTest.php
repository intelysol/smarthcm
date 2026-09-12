<?php

namespace Tests\Feature\Recruitment;

use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentInterview;
use App\Domains\Recruitment\Models\HcmRecruitmentInterviewEvaluation;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Recruitment\Services\RecruitmentSecurityService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruitmentSecurityAndConfidentialityTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_isolation_confidential_requisition_and_scorecard_privacy(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
        $userB = User::factory()->create(['tenant_id' => $tenantB->id]);

        $security = new RecruitmentSecurityService();

        // 1. Cross-tenant candidate access
        $candidateA = HcmRecruitmentCandidate::create([
            'tenant_id' => $tenantA->id,
            'candidate_number' => 'CAN-SEC-A',
            'first_name' => 'Alice',
            'last_name' => 'A',
            'email' => 'alice.a@example.com',
            'status' => 'active',
        ]);

        try {
            $security->authorizeCandidateAccess($userB, $candidateA);
            $this->fail('Expected AuthorizationException on cross-tenant access');
        } catch (AuthorizationException $e) {
            $this->assertStringContainsString('Cross-tenant candidate access prohibited', $e->getMessage());
        }

        // 2. Confidential Requisition gating
        $confidentialReq = HcmRecruitmentRequisition::create([
            'tenant_id' => $tenantA->id,
            'requisition_number' => 'REQ-CONFIDENTIAL',
            'title' => 'Chief Executive Officer Replacement',
            'is_confidential' => true,
            'recruiter_id' => $userA->id,
            'status' => 'open',
        ]);

        $otherUserInSameTenant = User::factory()->create([
            'tenant_id' => $tenantA->id,
            'is_platform_admin' => false,
        ]);

        try {
            $security->authorizeRequisitionAccess($otherUserInSameTenant, $confidentialReq);
            $this->fail('Expected AuthorizationException for confidential requisition');
        } catch (AuthorizationException $e) {
            $this->assertStringContainsString('Confidential requisition restricted', $e->getMessage());
        }

        // 3. Scorecard Sanitization
        $evaluation = new HcmRecruitmentInterviewEvaluation([
            'overall_score' => 4.8,
            'recommendation' => 'strong_yes',
            'confidential_notes' => 'Internal HR compensation note: Do not pay above band midpoint.',
            'submitted_at' => now(),
        ]);

        $sanitized = $security->sanitizeScorecardForCandidate($evaluation);
        $this->assertEquals(4.8, $sanitized['overall_score']);
        $this->assertEquals('strong_yes', $sanitized['recommendation']);
        $this->assertArrayNotHasKey('confidential_notes', $sanitized);
    }
}
