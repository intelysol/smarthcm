<?php

namespace Tests\Unit\Career;

use App\Domains\Career\Enums\SuccessionRiskLevel;
use App\Domains\Career\Models\SuccessionCandidate;
use App\Domains\Career\Models\SuccessionPlan;
use App\Domains\Career\Models\SuccessionPosition;
use App\Domains\Career\Services\SuccessionRiskEngine;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Job;
use App\Domains\Organization\Models\Position;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuccessionRiskEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_position_risk_and_succession_coverage_calculation(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $incumbent = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-EXEC-1',
            'employee_code' => 'EMP-EXEC-1',
            'first_name' => 'Usman',
            'last_name' => 'Ghani',
            'joining_date' => now()->subYears(10)->toDateString(),
        ]);

        $successor = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-SUCC-1',
            'employee_code' => 'EMP-SUCC-1',
            'first_name' => 'Bilal',
            'last_name' => 'Tariq',
            'joining_date' => now()->subYears(5)->toDateString(),
        ]);

        $job = Job::query()->create([
            'tenant_id' => $tenant->id,
            'job_code' => 'JOB-VP-ENG',
            'title' => 'VP of Engineering',
        ]);

        $position = Position::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'job_id' => $job->id,
            'code' => 'POS-VP-01',
            'position_code' => 'POS-VP-01',
            'title' => 'VP of Engineering',
            'headcount' => 1,
        ]);

        $plan = SuccessionPlan::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Executive Leadership Succession 2026',
            'status' => 'active',
        ]);

        $succPosition = SuccessionPosition::query()->create([
            'tenant_id' => $tenant->id,
            'succession_plan_id' => $plan->id,
            'position_id' => $position->id,
            'job_id' => $job->id,
            'current_incumbent_id' => $incumbent->id,
            'criticality' => 'business_critical',
            'vacancy_risk' => 'high',
        ]);

        $riskEngine = app(SuccessionRiskEngine::class);

        // 1. Without any candidate -> High/Critical Risk
        $riskBefore = $riskEngine->calculatePositionRisk($succPosition);
        $this->assertEquals(SuccessionRiskLevel::Critical->value, $riskBefore['risk_level']);

        // 2. Add ready-now candidate
        SuccessionCandidate::query()->create([
            'tenant_id' => $tenant->id,
            'succession_position_id' => $succPosition->id,
            'employee_id' => $successor->id,
            'priority' => 1,
            'readiness_timeframe' => 'ready_now',
            'readiness_score' => 95.0,
        ]);

        $succPosition->load('candidates');
        $riskAfter = $riskEngine->calculatePositionRisk($succPosition);

        // Depth 1 (25) + Ready Now (5) + Criticality (15) + Vacancy (12) = 57 (High) instead of Critical
        $this->assertLessThan($riskBefore['risk_score'], $riskAfter['risk_score']);

        // 3. Test Coverage Calculation
        $coverage = $riskEngine->calculateCoverage($tenant->id);
        $this->assertEquals(1, $coverage['total_critical_positions']);
        $this->assertEquals(1, $coverage['positions_with_successors']);
        $this->assertEquals(100.0, $coverage['coverage_rate']);
    }
}
