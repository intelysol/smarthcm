<?php

namespace Tests\Feature\WorkforceIntelligence;

use App\Domains\WorkforceIntelligence\Services\WorkforceHealthIndexService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkforceHealthIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_index_computes_with_transparent_weights_and_diagnosis(): void
    {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Acme Global',
            'slug' => 'acme-global',
            'tenant_code' => 'ACM001',
            'status' => 'active',
        ]);

        $service = app(WorkforceHealthIndexService::class);
        $healthData = $service->computeHealthIndex($tenant->id);

        $this->assertGreaterThanOrEqual(0, $healthData->compositeScore);
        $this->assertLessThanOrEqual(100, $healthData->compositeScore);
        $this->assertNotEmpty($healthData->summaryDiagnosis);
        $this->assertCount(6, $healthData->contributingFactors);

        // Verify API endpoint
        $response = $this->withHeaders(['X-Tenant-ID' => $tenant->id])
            ->getJson('/api/hcm/workforce-intelligence/health-index');

        $response->assertStatus(200);
        $response->assertJsonPath('confidence_score', 95);
    }
}
