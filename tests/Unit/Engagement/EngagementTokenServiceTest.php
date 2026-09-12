<?php

namespace Tests\Unit\Engagement;

use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementSurvey;
use App\Domains\Engagement\Services\EngagementTokenService;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementTokenServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_lifecycle_and_single_use_burn(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);

        $survey = EngagementSurvey::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'SURV-TOK',
            'title' => 'Token Survey',
            'confidentiality_type' => 'anonymous',
        ]);

        $campaign = EngagementCampaign::query()->create([
            'tenant_id' => $tenant->id,
            'survey_id' => $survey->id,
            'code' => 'CAMP-TOK',
            'name' => 'Token Campaign',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
        ]);

        $tokenService = new EngagementTokenService();

        // 1. Generate Token
        $rawToken = $tokenService->generateToken($campaign, 7);
        $this->assertNotEmpty($rawToken);

        // 2. Validate Token exists & is valid
        $this->assertTrue($tokenService->validateToken($campaign, $rawToken));

        // 3. Burn Token on single use
        $tokenService->burnToken($campaign, $rawToken);

        // 4. Burned token cannot be reused
        $this->assertFalse($tokenService->validateToken($campaign, $rawToken));

        // 5. Invalid random string cannot validate
        $this->assertFalse($tokenService->validateToken($campaign, 'invalid_random_string_xyz'));
    }
}
