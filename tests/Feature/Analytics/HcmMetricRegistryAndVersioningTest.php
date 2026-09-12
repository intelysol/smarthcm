<?php

namespace Tests\Feature\Analytics;

use App\Domains\Analytics\Models\HcmAnalyticsMetric;
use App\Domains\Analytics\Services\HcmMetricRegistryService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HcmMetricRegistryAndVersioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_metric_catalog_registration_and_versioning(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $registryService = app(HcmMetricRegistryService::class);

        // 1. Create a new HCM Metric
        $metric = $registryService->createMetric($tenant->id, [
            'code' => 'HCM_TURNOVER_RATE',
            'name' => 'Annualized Employee Turnover Rate',
            'category' => 'turnover',
            'unit' => 'percentage',
            'aggregation' => 'avg',
            'formula' => '(Total Exits / Average Headcount) * 100',
            'effective_from' => '2025-01-01',
        ], $user);

        $this->assertEquals('HCM_TURNOVER_RATE', $metric->code);
        $this->assertEquals(1, $metric->current_version);
        $this->assertCount(1, $metric->versions);

        // 2. Create Version 2 with refined formula
        $v2 = $registryService->createNewVersion(
            $metric,
            ['formula' => '(Total Exits / ((Opening + Closing) / 2)) * 100', 'aggregation' => 'avg'],
            '2026-01-01',
            'Updated formula to use opening and closing headcount average',
            $user
        );

        $this->assertEquals(2, $v2->version_number);
        $metric->refresh();
        $this->assertEquals(2, $metric->current_version);

        // 3. Verify Version Resolution for historical dates
        $historicalVersion = $registryService->getVersionForDate($metric, '2025-06-15');
        $this->assertEquals(1, $historicalVersion->version_number);

        $currentVersion = $registryService->getVersionForDate($metric, '2026-06-15');
        $this->assertEquals(2, $currentVersion->version_number);
    }
}
