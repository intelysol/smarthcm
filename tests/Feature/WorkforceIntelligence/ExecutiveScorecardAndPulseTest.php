<?php

namespace Tests\Feature\WorkforceIntelligence;

use App\Domains\WorkforceIntelligence\Contracts\WorkforceCommandCenterInterface;
use App\Domains\WorkforceIntelligence\Models\CommandCenterKpi;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExecutiveScorecardAndPulseTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_retrieve_executive_scorecard_and_operational_pulse(): void
    {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Acme Global',
            'slug' => 'acme-global',
            'tenant_code' => 'ACM001',
            'status' => 'active',
        ]);

        $commandCenter = app(WorkforceCommandCenterInterface::class);

        $scorecard = $commandCenter->getExecutiveScorecard($tenant->id);
        $this->assertNotNull($scorecard);
        $this->assertIsFloat($scorecard->compositeHealthScore);
        $this->assertContains($scorecard->healthBand, ['OPTIMAL', 'STABLE', 'AT_RISK', 'CRITICAL']);

        $pulse = $commandCenter->getWorkforcePulse($tenant->id);
        $this->assertNotNull($pulse);
        $this->assertNotEmpty($pulse->dataFreshnessTimestamp);

        // Test API Endpoint
        $response = $this->withHeaders(['X-Tenant-ID' => $tenant->id])
            ->getJson('/api/hcm/workforce-intelligence/scorecard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_headcount',
            'total_fte',
            'total_workforce_cost',
            'composite_health_score',
            'health_band',
            'overall_productivity_score',
        ]);
    }
}
