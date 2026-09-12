<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Enums\RetirementPlanType;
use App\Domains\Benefits\Models\RetirementPlan;
use App\Domains\Benefits\Services\RetirementContributionService;
use App\Domains\Benefits\Services\RetirementEnrollmentService;
use App\Domains\Benefits\Services\RetirementPlanService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetirementPlanAndContributionEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_retirement_plan_enrollment_and_contribution_matching_engine(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $planService = app(RetirementPlanService::class);
        $plan = $planService->createPlan([
            'tenant_id' => $tenant->id,
            'plan_code' => '401K-MATCH',
            'name' => 'Corporate Retirement Plan (50% Match up to 6%)',
            'plan_type' => RetirementPlanType::DEFINED_CONTRIBUTION->value,
            'default_employee_rate' => 6.0000,
            'default_employer_match_rate' => 50.0000, // 50% match of employee contribution
            'max_employer_contribution_rate' => 3.0000, // 3% max
            'contribution_base' => 'basic_salary',
            'vesting_type' => 'graded',
        ]);

        $enrollmentService = app(RetirementEnrollmentService::class);
        $enrollment = $enrollmentService->enrollEmployee($employee, $plan, [
            'employee_contribution_rate' => 6.0000,
            'voluntary_additional_amount' => 50.0000,
        ]);

        $this->assertEquals(6.0000, (float) $enrollment->employee_contribution_rate);
        $this->assertEquals(3.0000, (float) $enrollment->employer_contribution_rate);

        // Calculate Monthly Contribution on $10,000 Basic Salary
        // Employee = 6% of $10,000 + $50 = $650.00
        // Employer = 3% of $10,000 = $300.00
        // Total = $950.00
        $contribService = app(RetirementContributionService::class);
        $tx = $contribService->calculateAndPostMonthlyContribution($employee, $plan, 10000.0000);

        $this->assertEquals(950.0000, (float) $tx->amount);
        $this->assertEquals(950.0000, (float) $tx->running_balance);

        // Second month contribution -> Balance becomes $1,900.00
        $tx2 = $contribService->calculateAndPostMonthlyContribution($employee, $plan, 10000.0000);
        $this->assertEquals(950.0000, (float) $tx2->amount);
        $this->assertEquals(1900.0000, (float) $tx2->running_balance);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Edward',
            'last_name' => 'Norton',
            'official_email' => 'edward.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
