<?php

namespace Tests\Feature\Recruitment;

use App\Domains\Recruitment\Enums\InterviewRecommendation;
use App\Domains\Recruitment\Enums\InterviewType;
use App\Domains\Recruitment\Models\HcmRecruitmentApplication;
use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentInterview;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Recruitment\Services\InterviewService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterviewSchedulingAndEvaluationScorecardTest extends TestCase
{
    use RefreshDatabase;

    public function test_interview_panel_scheduling_and_scorecard_evaluation(): void
    {
        $tenant = Tenant::factory()->create();
        $leadInterviewer = User::factory()->create(['tenant_id' => $tenant->id]);
        $peerInterviewer = User::factory()->create(['tenant_id' => $tenant->id]);

        $requisition = HcmRecruitmentRequisition::create([
            'tenant_id' => $tenant->id,
            'requisition_number' => 'REQ-INT-01',
            'title' => 'Security Architect',
            'status' => 'open',
        ]);

        $candidate = HcmRecruitmentCandidate::create([
            'tenant_id' => $tenant->id,
            'candidate_number' => 'CAN-INT-01',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
            'status' => 'active',
        ]);

        $application = HcmRecruitmentApplication::create([
            'tenant_id' => $tenant->id,
            'application_number' => 'APP-INT-01',
            'candidate_id' => $candidate->id,
            'requisition_id' => $requisition->id,
            'status' => 'interview',
            'applied_at' => now(),
        ]);

        $service = new InterviewService();

        // 1. Schedule Interview
        $interview = $service->scheduleInterview($application, [
            'title' => 'Technical Architecture Panel',
            'interview_type' => InterviewType::PANEL->value,
            'scheduled_at' => now()->addDays(2),
            'duration_minutes' => 60,
            'meeting_url' => 'https://meet.example.com/sec-arch-ada',
        ], [$leadInterviewer->id, $peerInterviewer->id]);

        $this->assertInstanceOf(HcmRecruitmentInterview::class, $interview);
        $this->assertCount(2, $interview->participants);

        // 2. Submit Scorecards
        $eval1 = $service->submitEvaluation($interview, $leadInterviewer->id, [
            'technical_rating' => 4.5,
            'communication_rating' => 4.0,
            'problem_solving_rating' => 4.5,
            'recommendation' => InterviewRecommendation::STRONG_YES->value,
            'strengths' => 'Deep mastery of distributed security and cryptography.',
            'confidential_notes' => 'Top 1% candidate for architectural leadership.',
        ]);

        $eval2 = $service->submitEvaluation($interview, $peerInterviewer->id, [
            'technical_rating' => 4.0,
            'communication_rating' => 4.5,
            'problem_solving_rating' => 4.0,
            'recommendation' => InterviewRecommendation::YES->value,
            'strengths' => 'Excellent communication and collaborative problem breakdown.',
        ]);

        $this->assertEquals(4.3, $eval1->overall_score);
        $this->assertEquals(4.2, $eval2->overall_score);

        // 3. Interview Summary
        $summary = $service->getInterviewSummary($interview);
        $this->assertEquals(2, $summary['evaluations_count']);
        $this->assertTrue($summary['is_completed']);
        $this->assertEquals(4.3, $summary['average_score']);
        $this->assertEquals(1, $summary['recommendations_breakdown'][InterviewRecommendation::STRONG_YES->value]);
    }
}
