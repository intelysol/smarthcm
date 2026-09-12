<?php

namespace Tests\Feature\Engagement;

use App\Domains\Employee\Models\Employee;
use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementResponse;
use App\Domains\Engagement\Models\EngagementSurvey;
use App\Domains\Engagement\Models\EngagementSurveyQuestion;
use App\Domains\Engagement\Services\EngagementCampaignService;
use App\Domains\Engagement\Services\EngagementResponseService;
use App\Domains\Engagement\Services\EngagementSurveyService;
use App\Domains\Organization\Models\Company;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\EngagementPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NamedAndConfidentialSurveyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EngagementPermissionSeeder::class);
    }

    public function test_named_survey_records_respondent_and_prevents_duplicates(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-NAMED-01',
            'employee_code' => 'EMP-NAMED-01',
            'first_name' => 'Ali',
            'last_name' => 'Raza',
            'joining_date' => now()->toDateString(),
        ]);

        $survey = EngagementSurvey::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'SURV-NAMED-01',
            'title' => 'Manager 360 Feedback (Named)',
            'survey_type' => 'manager_feedback',
            'confidentiality_type' => 'named',
            'allow_multiple_responses' => false,
            'status' => 'open',
        ]);

        $q = EngagementSurveyQuestion::query()->create([
            'tenant_id' => $tenant->id,
            'survey_id' => $survey->id,
            'question' => 'My manager provides constructive feedback.',
            'question_type' => 'likert',
            'category' => 'leadership',
            'dimension' => 'leadership',
            'scale_config' => ['min' => 1, 'max' => 5],
        ]);

        $campaign = EngagementCampaign::query()->create([
            'tenant_id' => $tenant->id,
            'survey_id' => $survey->id,
            'code' => 'CAMP-NAMED-01',
            'name' => 'Manager Feedback Q3',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'status' => 'active',
        ]);

        // Submit via ESS API
        $response = $this->actingAs($user)->postJson("/api/v1/hcm/me/engagement/surveys/{$campaign->id}/submit", [
            'answers' => [
                [
                    'question_id' => $q->id,
                    'numeric_value' => 5,
                    'text_value' => 'Always helpful guidance.',
                ],
            ],
        ]);

        $response->assertStatus(200);

        // Verify database has named employee_id
        $this->assertDatabaseHas('engagement_responses', [
            'campaign_id' => $campaign->id,
            'employee_id' => $employee->id,
            'confidentiality_type' => 'named',
            'response_status' => 'submitted',
            'is_locked' => true,
        ]);

        // Attempt second submission when multiple responses disallowed -> should reject
        $dupResponse = $this->actingAs($user)->postJson("/api/v1/hcm/me/engagement/surveys/{$campaign->id}/submit", [
            'answers' => [
                [
                    'question_id' => $q->id,
                    'numeric_value' => 4,
                ],
            ],
        ]);

        $dupResponse->assertStatus(500); // Throws InvalidArgumentException caught by Laravel handler
    }
}
