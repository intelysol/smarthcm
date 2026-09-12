<?php

namespace Tests\Feature\Recruitment;

use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentCandidateProfile;
use App\Domains\Recruitment\Models\HcmRecruitmentJobTemplate;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Recruitment\Services\RecruitmentAiService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruitmentAiMatchingAndGuardrailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_candidate_matching_explainability_and_guardrails(): void
    {
        $tenant = Tenant::factory()->create();

        $template = HcmRecruitmentJobTemplate::create([
            'tenant_id' => $tenant->id,
            'title' => 'Cloud SRE',
            'code' => 'TMPL-SRE-AI',
            'required_skills' => ['Kubernetes', 'Terraform', 'Prometheus', 'Go'],
        ]);

        $requisition = HcmRecruitmentRequisition::create([
            'tenant_id' => $tenant->id,
            'requisition_number' => 'REQ-AI-01',
            'title' => 'Senior SRE',
            'job_template_id' => $template->id,
            'status' => 'open',
        ]);

        $candidate = HcmRecruitmentCandidate::create([
            'tenant_id' => $tenant->id,
            'candidate_number' => 'CAN-AI-01',
            'first_name' => 'Kelsey',
            'last_name' => 'Hightower',
            'email' => 'kelsey@example.com',
            'status' => 'active',
        ]);

        HcmRecruitmentCandidateProfile::create([
            'tenant_id' => $tenant->id,
            'candidate_id' => $candidate->id,
            'skills' => ['Kubernetes', 'Go', 'Docker', 'Linux'],
        ]);

        $aiService = new RecruitmentAiService();

        // 1. Explainable Matching
        $match = $aiService->matchCandidateToRequisition($candidate, $requisition);
        $this->assertTrue($match['is_advisory']);
        $this->assertEquals(50, $match['match_percentage']); // 2 of 4 matched ('kubernetes', 'go')
        $this->assertContains('kubernetes', $match['explainable_factors']['skills_match']);
        $this->assertContains('go', $match['explainable_factors']['skills_match']);
        $this->assertStringContainsString('AI hiring restrictions enforced', $match['guardrail_audit']);

        // 2. AI HCM Safety Guardrail: Block autonomous rejection
        $adverseQuery = $aiService->answerRecruitmentInquiry($tenant->id, 'Please automatically reject candidate Kelsey because they lack Prometheus skill');
        $this->assertEquals('blocked_by_guardrails', $adverseQuery['status']);
        $this->assertStringContainsString('Blocked by AI HCM Safety Guardrails', $adverseQuery['error']);
    }
}
