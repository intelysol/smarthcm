<?php

namespace Tests\Unit\Engagement;

use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementResponse;
use App\Domains\Engagement\Models\EngagementResponseAnswer;
use App\Domains\Engagement\Models\EngagementSurvey;
use App\Domains\Engagement\Models\EngagementSurveyQuestion;
use App\Domains\Engagement\Services\EngagementPrivacyService;
use App\Domains\Engagement\Services\EngagementResultService;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementResultServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_enps_calculation_formula(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);

        $survey = EngagementSurvey::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'SURV-NPS-TEST',
            'title' => 'eNPS Test Survey',
            'survey_type' => 'pulse',
            'confidentiality_type' => 'anonymous',
        ]);

        $npsQuestion = EngagementSurveyQuestion::query()->create([
            'tenant_id' => $tenant->id,
            'survey_id' => $survey->id,
            'question' => 'How likely are you to recommend working here?',
            'question_type' => 'nps',
            'category' => 'engagement',
            'dimension' => 'engagement',
        ]);

        $campaign = EngagementCampaign::query()->create([
            'tenant_id' => $tenant->id,
            'survey_id' => $survey->id,
            'code' => 'CAMP-NPS-01',
            'name' => 'Q3 eNPS Pulse',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'status' => 'active',
            'minimum_response_threshold' => 5,
        ]);

        // Create 10 responses:
        // 5 Promoters (Score 9, 10, 10, 9, 10) -> 50%
        // 3 Passives (Score 7, 8, 7) -> 30%
        // 2 Detractors (Score 3, 5) -> 20%
        // Expected eNPS = 50 - 20 = +30.0
        $scores = [10, 9, 10, 9, 10, 8, 7, 7, 5, 3];

        foreach ($scores as $score) {
            $resp = EngagementResponse::query()->create([
                'tenant_id' => $tenant->id,
                'survey_id' => $survey->id,
                'campaign_id' => $campaign->id,
                'confidentiality_type' => 'anonymous',
                'response_status' => 'submitted',
                'started_at' => now(),
                'submitted_at' => now(),
                'is_locked' => true,
            ]);

            EngagementResponseAnswer::query()->create([
                'tenant_id' => $tenant->id,
                'response_id' => $resp->id,
                'question_id' => $npsQuestion->id,
                'numeric_value' => $score,
            ]);
        }

        $service = app(EngagementResultService::class);
        $npsResult = $service->calculateNps($campaign);

        $this->assertEquals(10, $npsResult['total_nps_responses']);
        $this->assertEquals(5, $npsResult['promoters']);
        $this->assertEquals(3, $npsResult['passives']);
        $this->assertEquals(2, $npsResult['detractors']);
        $this->assertEquals(50.0, $npsResult['promoter_percentage']);
        $this->assertEquals(30.0, $npsResult['passive_percentage']);
        $this->assertEquals(20.0, $npsResult['detractor_percentage']);
        $this->assertEquals(30.0, $npsResult['nps_score']);
    }

    public function test_dimension_score_and_favorability(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);

        $survey = EngagementSurvey::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'SURV-DIM-TEST',
            'title' => 'Dimension Test',
            'survey_type' => 'engagement',
        ]);

        $q1 = EngagementSurveyQuestion::query()->create([
            'tenant_id' => $tenant->id,
            'survey_id' => $survey->id,
            'question' => 'My leader communicates transparently.',
            'question_type' => 'likert',
            'category' => 'leadership',
            'dimension' => 'leadership',
            'scale_config' => ['min' => 1, 'max' => 5],
        ]);

        $campaign = EngagementCampaign::query()->create([
            'tenant_id' => $tenant->id,
            'survey_id' => $survey->id,
            'code' => 'CAMP-DIM-01',
            'name' => 'Annual Culture Survey',
            'start_date' => now()->subDays(2)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'status' => 'active',
        ]);

        // 4 favorable responses (5, 4, 5, 4), 1 neutral (3), 1 unfavorable (2)
        // Total = 6, Favorable = 4 -> Favorability = (4/6)*100 = 66.67%
        $answers = [
            ['score' => 5, 'fav' => 'favorable'],
            ['score' => 4, 'fav' => 'favorable'],
            ['score' => 5, 'fav' => 'favorable'],
            ['score' => 4, 'fav' => 'favorable'],
            ['score' => 3, 'fav' => 'neutral'],
            ['score' => 2, 'fav' => 'unfavorable'],
        ];

        foreach ($answers as $a) {
            $resp = EngagementResponse::query()->create([
                'tenant_id' => $tenant->id,
                'survey_id' => $survey->id,
                'campaign_id' => $campaign->id,
                'response_status' => 'submitted',
                'started_at' => now(),
                'submitted_at' => now(),
                'is_locked' => true,
            ]);

            EngagementResponseAnswer::query()->create([
                'tenant_id' => $tenant->id,
                'response_id' => $resp->id,
                'question_id' => $q1->id,
                'numeric_value' => $a['score'],
                'favorability_status' => $a['fav'],
            ]);
        }

        $service = app(EngagementResultService::class);
        $dimScores = $service->getDimensionScores($campaign);

        $this->assertArrayHasKey('leadership', $dimScores);
        $this->assertEquals(6, $dimScores['leadership']['sample_size']);
        $this->assertEquals(3.83, $dimScores['leadership']['average_score']);
        $this->assertEquals(66.67, $dimScores['leadership']['favorability_rate']);
    }
}
