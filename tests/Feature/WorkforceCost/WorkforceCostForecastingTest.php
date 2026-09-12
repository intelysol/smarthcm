<?php

namespace Tests\Feature\WorkforceCost;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceCost\Services\LaborCostAggregationService;
use App\Domains\WorkforceCost\Services\WorkforceCostForecastService;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceCostForecastingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_workforce_cost_forecast_generation_with_assumptions(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $aggService = app(LaborCostAggregationService::class);
        $forecastService = app(WorkforceCostForecastService::class);

        // Prior month baseline: $100,000 in lines
        $aggService->createCostLine($tenant->id, [
            'cost_category' => 'direct_labor',
            'component_type' => 'BASE_PAY',
            'cost_date' => '2026-09-15',
            'amount' => 100000.00,
        ]);

        // Generate forecast for October 2026
        // Assumptions: Headcount growth +5%, Salary increase +3%, Overtime +2%, Contractor +1%
        $forecast = $forecastService->generateForecast(
            $tenant->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-10-31'),
            null,
            [
                'headcount_growth_percent' => 5.0,
                'salary_increase_percent' => 3.0,
                'overtime_factor_percent' => 2.0,
                'contractor_factor_percent' => 1.0,
            ]
        );

        $this->assertNotNull($forecast);
        $this->assertEquals(100000.00, (float) $forecast->baseline_amount);
        $this->assertEquals(5000.00, (float) $forecast->headcount_impact_amount);
        $this->assertEquals(3000.00, (float) $forecast->salary_increase_impact_amount);
        $this->assertEquals(2000.00, (float) $forecast->overtime_impact_amount);
        $this->assertEquals(1000.00, (float) $forecast->contractor_impact_amount);
        // Total forecast = 100,000 + 5000 + 3000 + 2000 + 1000 = 111,000.00
        $this->assertEquals(111000.00, (float) $forecast->forecast_total_cost);

        // API Endpoint test
        $response = $this->actingAs($user)->postJson('/api/v1/hcm/workforce-cost/forecasts/generate', [
            'tenant_id' => $tenant->id,
            'forecast_start' => '2026-11-01',
            'forecast_end' => '2026-11-30',
            'assumptions' => [
                'headcount_growth_percent' => 4.0,
            ],
        ]);
        $response->assertStatus(201);
    }
}