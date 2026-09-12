<?php

declare(strict_types=1);

namespace Tests\Feature\Compensation;

use App\Domains\Compensation\Models\CompensationCycle;
use App\Domains\Compensation\Models\CompensationRecommendation;
use App\Domains\Compensation\Services\CompensationAiAdvisoryService;
use App\Domains\Compensation\Services\PayEquityAnalyticsService;
use App\Domains\Compensation\Services\TotalRewardsService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\CompensationPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TotalRewardsAndAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompensationPermissionSeeder::class);
    }

    public function test_total_rewards_statement_generation(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);

        $company = \App\Domains\Organization\Models\Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-TOT-001',
            'first_name' => 'Eve',
            'last_name' => 'Adams',
            'employment_status' => 'active',
            'joining_date' => '2021-06-01',
        ]);

        $service = app(TotalRewardsService::class);
        $statement = $service->generateStatement(
            $user,
            $employee,
            2026,
            120000.0, // Base
            20000.0,  // Bonus
            15000.0,  // Health & benefits
            25000.0,  // Equity
            10000.0,  // Retirement
            5000.0    // Allowances
        );

        $this->assertEquals(195000.0, (float) $statement->total_employer_investment);
        $this->assertEquals(2026, $statement->year);
        $this->assertArrayHasKey('base_pay', $statement->components);
        $this->assertArrayHasKey('health_and_wellness', $statement->components);
        $this->assertArrayHasKey('equity_and_ownership', $statement->components);
        // Base pay percentage = 120000 / 195000 * 100 = 61.54%
        $this->assertEquals(61.54, $statement->components['base_pay']['percentage']);
    }

    public function test_pay_equity_analytics(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $company = \App\Domains\Organization\Models\Company::factory()->create(['tenant_id' => $tenant->id]);

        $maleEmp = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-PE-001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'male',
            'employment_status' => 'active',
            'joining_date' => '2020-01-01',
        ]);

        $femaleEmp = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-PE-002',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'gender' => 'female',
            'employment_status' => 'active',
            'joining_date' => '2020-01-01',
        ]);

        $cycle = CompensationCycle::create([
            'tenant_id' => $tenant->id,
            'name' => 'Equity Analysis Cycle',
            'cycle_type' => 'annual_merit',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'effective_on' => '2026-04-01',
            'status' => 'hr_review',
        ]);

        CompensationRecommendation::create([
            'tenant_id' => $tenant->id,
            'compensation_cycle_id' => $cycle->id,
            'employee_id' => $maleEmp->id,
            'current_base_salary' => 100000.0,
            'recommended_base_salary' => 105000.0,
            'increase_amount' => 5000.0,
            'increase_percentage' => 5.0,
            'compa_ratio_current' => 1.0,
            'recommendation_type' => 'merit',
        ]);

        CompensationRecommendation::create([
            'tenant_id' => $tenant->id,
            'compensation_cycle_id' => $cycle->id,
            'employee_id' => $femaleEmp->id,
            'current_base_salary' => 100000.0,
            'recommended_base_salary' => 105000.0,
            'increase_amount' => 5000.0,
            'increase_percentage' => 5.0,
            'compa_ratio_current' => 1.0,
            'recommendation_type' => 'merit',
        ]);

        $analytics = app(PayEquityAnalyticsService::class);
        $result = $analytics->analyzeCycleEquity($cycle);

        $this->assertEquals(2, $result['total_reviewed']);
        $this->assertEquals(0.0, $result['pay_gap_percentage']); // Perfect parity
        $this->assertEquals(1.0, $result['overall_average_compa_ratio']);
    }

    public function test_ai_advisory_compliance(): void
    {
        $aiService = app(CompensationAiAdvisoryService::class);

        $advice = $aiService->draftMeritJustification('Alice Smith', 'exceeds', 0.95, 6.0, 'Led mobile redesign');
        $this->assertTrue($advice['is_advisory']);
        $this->assertStringContainsString('Alice Smith', $advice['suggested_justification']);
        $this->assertStringContainsString('exceeds', $advice['suggested_justification']);

        $varianceAdvice = $aiService->explainBudgetVariance(100000, 105000, -5000);
        $this->assertTrue($varianceAdvice['is_advisory']);
        $this->assertEquals('over allocation', $varianceAdvice['status']);
    }
}
