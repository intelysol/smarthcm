<?php

namespace Tests\Feature\Recruitment;

use App\Domains\Recruitment\Enums\ApplicationStatus;
use App\Domains\Recruitment\Models\HcmRecruitmentApplication;
use App\Domains\Recruitment\Models\HcmRecruitmentApplicationStage;
use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Recruitment\Services\ApplicationService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ApplicationPipelineAndScreeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_submission_pipeline_transition_and_screening(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $stage1 = HcmRecruitmentApplicationStage::create([
            'tenant_id' => $tenant->id,
            'name' => 'New',
            'code' => 'NEW',
            'stage_order' => 1,
        ]);
        $stage2 = HcmRecruitmentApplicationStage::create([
            'tenant_id' => $tenant->id,
            'name' => 'Screening',
            'code' => 'SCREENING',
            'stage_order' => 2,
        ]);

        $requisition = HcmRecruitmentRequisition::create([
            'tenant_id' => $tenant->id,
            'requisition_number' => 'REQ-TEST-01',
            'title' => 'DevOps Engineer',
            'status' => 'open',
        ]);

        $candidate = HcmRecruitmentCandidate::create([
            'tenant_id' => $tenant->id,
            'candidate_number' => 'CAN-TEST-01',
            'first_name' => 'Sam',
            'last_name' => 'Altman',
            'email' => 'sam@example.com',
            'status' => 'active',
        ]);

        $service = new ApplicationService();

        // 1. Submit application
        $app = $service->apply($candidate, $requisition, [
            'cover_letter' => 'Excited to contribute to your infrastructure.',
            'actor_id' => $user->id,
        ]);

        $this->assertInstanceOf(HcmRecruitmentApplication::class, $app);
        $this->assertEquals(ApplicationStatus::NEW->value, $app->status);
        $this->assertCount(1, $app->activities);

        // 2. Prevent duplicate application
        try {
            $service->apply($candidate, $requisition, []);
            $this->fail('Expected ValidationException on duplicate application');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('application', $e->errors());
        }

        // 3. Transition stage
        $transitioned = $service->transitionStage($app, $stage2, $user->id, 'Moved to screening phase');
        $this->assertEquals($stage2->id, $transitioned->stage_id);
        $this->assertCount(2, $transitioned->fresh()->activities);

        // 4. Screen application
        $screening = $service->screenApplication($transitioned, [
            'skills_match' => true,
            'experience_match' => true,
            'education_match' => true,
            'salary_match' => true,
            'result' => 'passed',
            'score' => 95.0,
            'feedback' => 'Candidate meets all technical prerequisites.',
        ], $user->id);

        $this->assertEquals('passed', $screening->result);
        $this->assertEquals(ApplicationStatus::SHORTLISTED->value, $transitioned->fresh()->status);
    }
}
