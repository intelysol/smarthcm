<?php

namespace Tests\Feature\WorkforceCost;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceCost\Services\LaborCostAggregationService;
use App\Domains\WorkforceCost\Services\WorkforceCostVarianceService;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceCostVarianceAndDriverAnalysisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_plan_vs_actual_variance_calculation_and_driver_analysis(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $aggService = app(LaborCostAggregationService::class);
        $varianceService = app(WorkforceCostVarianceService::class);

        // Actual cost: 120,000 (Base: 100k, OT: 20k)
        $aggService->createCostLine($tenant->id, [
            'cost_category' => 'direct_labor',
            'component_type' => 'BASE_PAY',
            'cost_date' => '2026-10-15',
            'amount' => 100000.00,
        ]);
        $aggService->createCostLine($tenant->id, [
            'cost_category' => 'direct_labor',
            'component_type' => 'OVERTIME',
            'cost_date' => '2026-10-20',
            'amount' => 20000.00,
        ]);

        // Planned cost: 100,000. Actual: 120,000. Variance = +20,000 (+20%)
        $variance = $varianceService->calculateVariance(
            $tenant->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-10-31'),
            'plan_vs_actual',
            100000.00
        );

        $this->assertNotNull($variance);
        $this->assertEquals(100000.00, (float) $variance->planned_amount);
        $this->assertEquals(120000.00, (float) $variance->actual_amount);
        $this->assertEquals(20000.00, (float) $variance->variance_amount);
        $this->assertEquals(20.00, (float) $variance->variance_percentage);

        $drivers = $variance->drivers;
        $this->assertIsArray($drivers);
        // Overtime contributed 20k / 120k * 100 = 16.67% of total spend
        $this->assertEquals(16.67, $drivers['overtime_contribution_pct']);

        // API Endpoint test
        $response = $this->actingAs($user)->postJson('/api/v1/hcm/workforce-cost/variances/calculate', [
            'tenant_id' => $tenant->id,
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'planned_amount' => 110000.00,
        ]);
        $response->assertStatus(201);
    }
}