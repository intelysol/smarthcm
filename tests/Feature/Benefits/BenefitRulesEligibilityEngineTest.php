<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Services\BenefitEligibilityService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\JobGrade;
use App\Domains\Shared\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BenefitRulesEligibilityEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_eligibility_evaluation_with_waiting_periods_and_auditing(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = \App\Domains\Organization\Models\BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'code' => 'BU-OPS',
            'name' => 'Operations Unit',
        ]);
        $dept = Department::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'OPS',
            'department_name' => 'Operations',
        ]);
        $grade = JobGrade::create([
            'tenant_id' => $tenant->id,
            'grade_code' => 'G-3',
            'grade_name' => 'Grade 3',
            'level' => 3,
        ]);

        // Employee 1: Joined 10 days ago (within 30-day waiting period)
        $empNewHire = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'job_grade_id' => $grade->id,
            'employee_code' => 'EMP-NEW',
            'employee_number' => '1001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'employment_status' => 'active',
            'joining_date' => Carbon::today()->subDays(10)->toDateString(),
        ]);

        // Employee 2: Joined 60 days ago (completed waiting period)
        $empTenured = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'job_grade_id' => $grade->id,
            'employee_code' => 'EMP-TEN',
            'employee_number' => '1002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'employment_status' => 'active',
            'joining_date' => Carbon::today()->subDays(60)->toDateString(),
        ]);

        $plan = BenefitPlan::create([
            'tenant_id' => $tenant->id,
            'code' => 'HEALTH-30D',
            'name' => 'Standard Health with 30-Day Waiting Period',
            'benefit_type' => 'medical',
            'employee_cost' => 30,
            'employer_cost' => 120,
            'effective_from' => '2026-01-01',
            'waiting_period_days' => 30,
            'status' => 'active',
        ]);

        $plan->eligibilityRules()->create([
            'tenant_id' => $tenant->id,
            'rule_name' => 'Ops Department Rule',
            'criteria' => [
                'department_ids' => [$dept->id],
            ],
            'is_active' => true,
        ]);

        $eligibilityService = app(BenefitEligibilityService::class);

        // 1. Evaluate Tenured employee -> Eligible
        $resTenured = $eligibilityService->evaluateEligibility($empTenured, $plan);
        $this->assertEquals('eligible', $resTenured->status);
        $this->assertDatabaseHas('benefit_eligibility_results', [
            'employee_id' => $empTenured->id,
            'benefit_plan_id' => $plan->id,
            'status' => 'eligible',
        ]);

        // 2. Evaluate New Hire employee -> Pending due to waiting period
        $resNewHire = $eligibilityService->evaluateEligibility($empNewHire, $plan);
        $this->assertEquals('pending', $resNewHire->status);
        $this->assertTrue($resNewHire->effective_date->isFuture());

        // 3. Evaluate Inactive employee -> Not Eligible
        $empNewHire->update(['employment_status' => 'suspended']);
        $resInactive = $eligibilityService->evaluateEligibility($empNewHire, $plan);
        $this->assertEquals('not_eligible', $resInactive->status);

        // 4. Batch Evaluate
        $stats = $eligibilityService->batchEvaluate($tenant->id, $plan->id);
        $this->assertEquals(1, $stats['eligible']); // empTenured
    }
}
