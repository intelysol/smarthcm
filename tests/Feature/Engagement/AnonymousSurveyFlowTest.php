<?php

namespace Tests\Feature\Engagement;

use App\Domains\Employee\Models\Employee;
use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementCampaignRecipient;
use App\Domains\Engagement\Models\EngagementResponse;
use App\Domains\Engagement\Models\EngagementSurvey;
use App\Domains\Engagement\Models\EngagementSurveyQuestion;
use App\Domains\Engagement\Services\EngagementCampaignService;
use App\Domains\Engagement\Services\EngagementResponseService;
use App\Domains\Engagement\Services\EngagementResultService;
use App\Domains\Engagement\Services\EngagementSurveyService;
use App\Domains\Engagement\Services\EngagementTokenService;
use App\Domains\Organization\Models\Company;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\EngagementPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymousSurveyFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EngagementPermissionSeeder::class);
    }

    public function test_end_to_end_anonymous_survey_workflow_guarantees_identity_isolation(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $adminUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $empUser = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $empUser->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-ANON-01',
            'employee_code' => 'EMP-ANON-01',
            'first_name' => 'Sara',
            'last_name' => 'Khan',
            'joining_date' => now()->subYears(2)->toDateString(),
        ]);

        $surveyService = app(EngagementSurveyService::class);
        $campaignService = app(EngagementCampaignService::class);
        $tokenService = app(EngagementTokenService::class);
        $responseService = app(EngagementResponseService::class);
        $resultService = app(EngagementResultService::class);

        // 1. Create Anonymous Survey
        $survey = $surveyService->createSurvey($tenant->id, [
            'code' => 'SURV-ANON-2026',
            'title' => '2026 Anonymous Engagement Survey',
            'survey_type' => 'engagement',
            'confidentiality_type' => 'anonymous',
        ], $adminUser->id);

        $q1 = $surveyService->addQuestion($survey, [
            'question' => 'I feel empowered to do my best work every day.',
            'question_type' => 'likert',
            'category' => 'growth',
            'dimension' => 'growth',
            'scale_config' => ['min' => 1, 'max' => 5],
        ]);

        $q2 = $surveyService->addQuestion($survey, [
            'question' => 'How likely are you to recommend Flow HCM to a peer?',
            'question_type' => 'nps',
            'category' => 'engagement',
            'dimension' => 'engagement',
        ]);

        // Publish Survey Version
        $surveyService->publishSurvey($survey, $adminUser->id);

        // 2. Create Campaign & Launch
        $campaign = $campaignService->createCampaign($tenant->id, $survey, [
            'code' => 'CAMP-ANON-2026',
            'name' => 'Annual 2026 Survey',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'minimum_response_threshold' => 1,
        ], $adminUser->id);

        $campaignService->launchCampaign($campaign, $adminUser->id);

        // Verify recipient was created
        $this->assertDatabaseHas('engagement_campaign_recipients', [
            'campaign_id' => $campaign->id,
            'employee_id' => $employee->id,
            'delivery_status' => 'delivered',
        ]);

        // 3. Generate Anonymous Participation Token
        $rawToken = $tokenService->generateToken($campaign);
        $this->assertTrue($tokenService->validateToken($campaign, $rawToken));

        // 4. Start & Submit Anonymous Response
        $response = $responseService->startResponse($campaign, $employee, $rawToken);

        // Identity decoupling assertion: employee_id MUST be null on the response!
        $this->assertNull($response->employee_id);
        $this->assertEquals('anonymous', $response->confidentiality_type);
        $this->assertEquals('in_progress', $response->response_status);

        // Submit Answers
        $submitted = $responseService->submitResponse($response, [
            [
                'question_id' => $q1->id,
                'numeric_value' => 5,
            ],
            [
                'question_id' => $q2->id,
                'numeric_value' => 10,
            ],
        ], $rawToken, $employee);

        // Assert response locked & submitted
        $this->assertEquals('submitted', $submitted->response_status);
        $this->assertTrue($submitted->is_locked);
        $this->assertNull($submitted->employee_id);

        // Token must now be burned
        $this->assertFalse($tokenService->validateToken($campaign, $rawToken));

        // 5. Query Aggregated Results
        $results = $resultService->getCampaignResults($campaign);
        $this->assertFalse($results['suppressed']);
        $this->assertEquals(1, $results['total_responses']);
        $this->assertEquals(100.0, $results['overall_favorability']);
        $this->assertEquals(100.0, $results['nps']['nps_score']);
    }
}
