<?php

namespace Tests\Feature\Analytics;

use App\Domains\Analytics\Services\HcmTalentAndRecruitmentAnalyticsService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HcmRecruitmentAndOnboardingAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_recruitment_funnel_and_talent_analytics(): void
    {
        $tenant = Tenant::factory()->create();
        $service = app(HcmTalentAndRecruitmentAnalyticsService::class);

        $funnel = $service->getRecruitmentFunnel($tenant->id, '2026-01-01', '2026-03-31');

        $this->assertArrayHasKey('funnel', $funnel);
        $this->assertCount(5, $funnel['funnel']);
        $this->assertEquals('Applications', $funnel['funnel'][0]['stage']);
        $this->assertEquals('Hires (Offers Accepted)', $funnel['funnel'][4]['stage']);
        $this->assertGreaterThanOrEqual(0, $funnel['average_time_to_hire_days']);

        $talent = $service->getTalentAndSuccessionSummary($tenant->id);
        $this->assertArrayHasKey('succession_coverage_percent', $talent);
        $this->assertGreaterThan(0, $talent['succession_coverage_percent']);
    }
}
