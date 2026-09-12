<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Models\PerformanceCycleConfiguration;
use App\Domains\Performance\Services\PerformanceCycleService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\PerformancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceCycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PerformancePermissionSeeder::class);
    }

    public function test_create_configure_and_transition_performance_cycle(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);

        $service = app(PerformanceCycleService::class);

        // 1. Create Draft Cycle
        $cycle = $service->create($admin, [
            'name' => '2026 Annual Performance Review',
            'description' => 'Enterprise performance appraisal cycle',
            'cycle_type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'draft',
        ]);

        $this->assertEquals('draft', $cycle->status);
        $this->assertNotNull($cycle->configuration);

        // 2. Configure Weights (Total 100%)
        $configured = $service->configure($admin, $cycle, [
            'goal_weight' => 70,
            'competency_weight' => 20,
            'feedback_weight' => 10,
            'self_assessment_enabled' => true,
            'manager_assessment_enabled' => true,
            'feedback_360_enabled' => true,
            'calibration_enabled' => true,
        ]);

        $this->assertEquals('configured', $configured->status);
        $this->assertEquals(70.0, (float) $configured->configuration->goal_weight);

        // 3. Transition to Open
        $opened = $service->transition($admin, $configured, 'open');
        $this->assertEquals('open', $opened->status);
    }
}
