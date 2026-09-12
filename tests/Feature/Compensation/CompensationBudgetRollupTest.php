<?php

declare(strict_types=1);

namespace Tests\Feature\Compensation;

use App\Domains\Compensation\Models\CompensationCycle;
use App\Domains\Compensation\Services\CompensationBudgetService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\CompensationPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CompensationBudgetRollupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompensationPermissionSeeder::class);
    }

    public function test_budget_allocation_and_utilization_rollup(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);

        $cycle = CompensationCycle::create([
            'tenant_id' => $tenant->id,
            'name' => '2026 Merit Cycle',
            'cycle_type' => 'annual_merit',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'effective_on' => '2026-04-01',
            'status' => 'open',
            'currency' => 'USD',
        ]);

        $budgetService = app(CompensationBudgetService::class);

        // 1. Allocate Engineering Budget ($100,000 with $5,000 holdback)
        $engBudget = $budgetService->allocate(
            $admin,
            $cycle,
            'department',
            'dept-eng-001',
            100000.0,
            'merit',
            5000.0
        );

        $this->assertEquals(100000.0, (float) $engBudget->allocated);
        $this->assertEquals(5000.0, (float) $engBudget->holdback_amount);
        $this->assertEquals(95000.0, $engBudget->remaining());
        $this->assertEquals(0.0, $engBudget->utilizationPercentage());

        // 2. Consume $40,000
        $budgetService->recordConsumption($engBudget, 40000.0);
        $engBudget->refresh();
        $this->assertEquals(40000.0, (float) $engBudget->consumed);
        $this->assertEquals(55000.0, $engBudget->remaining());
        $this->assertEquals(40.0, $engBudget->utilizationPercentage());

        // 3. Allocate Sales Budget ($50,000 with 0 holdback)
        $salesBudget = $budgetService->allocate(
            $admin,
            $cycle,
            'department',
            'dept-sales-002',
            50000.0,
            'merit',
            0.0
        );
        $budgetService->recordConsumption($salesBudget, 25000.0);

        // 4. Test Rollup across entire cycle
        $rollup = $budgetService->calculateRollup($cycle);
        $this->assertEquals(150000.0, $rollup['total_allocated']);
        $this->assertEquals(65000.0, $rollup['total_consumed']);
        $this->assertEquals(5000.0, $rollup['total_holdback']);
        $this->assertEquals(80000.0, $rollup['total_remaining']);
        // 65000 / 150000 * 100 = 43.33%
        $this->assertEquals(43.33, $rollup['utilization_percentage']);
        $this->assertFalse($rollup['is_over_budget']);
    }
}
