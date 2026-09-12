<?php

namespace Tests\Feature\WorkforceCost;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceCost\Services\AdvisoryWorkforceCostAiService;
use App\Domains\WorkforceCost\Services\LaborCostAggregationService;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdvisoryWorkforceCostAiSafetyAndRBACTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_advisory_ai_safety_and_tenant_isolation_rbc(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $companyA = Company::factory()->create(['tenant_id' => $tenantA->id]);
        $buA = BusinessUnit::create([
            'tenant_id' => $tenantA->id,
            'company_id' => $companyA->id,
            'name' => 'A Operations',
            'code' => 'A-OPS-' . Str::random(4),
        ]);
        $deptA = Department::create([
            'tenant_id' => $tenantA->id,
            'company_id' => $companyA->id,
            'business_unit_id' => $buA->id,
            'department_name' => 'Engineering',
            'department_code' => 'ENG-A-' . Str::random(4),
        ]);

        $userA = User::factory()->create(['tenant_id' => $tenantA->id]);

        $aggService = app(LaborCostAggregationService::class);
        $aiService = app(AdvisoryWorkforceCostAiService::class);

        // Create heavy overtime for Tenant A: 10k base, 3k OT (> 15%)
        $aggService->createCostLine($tenantA->id, [
            'cost_category' => 'direct_labor',
            'component_type' => 'BASE_PAY',
            'cost_date' => '2026-10-05',
            'amount' => 10000.00,
        ]);
        $aggService->createCostLine($tenantA->id, [
            'cost_category' => 'direct_labor',
            'component_type' => 'OVERTIME',
            'cost_date' => '2026-10-06',
            'amount' => 3000.00,
        ]);

        // Tenant B cost line
        $aggService->createCostLine($tenantB->id, [
            'cost_category' => 'direct_labor',
            'component_type' => 'BASE_PAY',
            'cost_date' => '2026-10-05',
            'amount' => 99999.00,
        ]);

        // Advisory AI verification
        $insights = $aiService->generateCostInsights($tenantA->id, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));

        $this->assertTrue($insights['is_advisory_only']);
        $this->assertFalse($insights['autonomous_actions_permitted']);
        $this->assertEquals(13000.00, $insights['metrics_summary']['total_cost']);
        // Must not contain Tenant B data
        $this->assertNotEquals(112999.00, $insights['metrics_summary']['total_cost']);

        // Anomaly detected
        $this->assertCount(1, $insights['anomalies_detected']);
        $this->assertEquals('elevated_overtime', $insights['anomalies_detected'][0]['type']);

        // API Endpoint test
        $response = $this->actingAs($userA)->getJson('/api/v1/hcm/workforce-cost/ai/insights?start_date=2026-10-01&end_date=2026-10-31&tenant_id=' . $tenantA->id);
        $response->assertStatus(200);
        $response->assertJsonPath('is_advisory_only', true);
    }
}