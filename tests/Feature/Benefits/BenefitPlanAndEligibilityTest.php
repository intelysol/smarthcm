<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Enums\BenefitCategoryType;
use App\Domains\Benefits\Models\BenefitCategory;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Models\BenefitProvider;
use App\Domains\Benefits\Services\BenefitEligibilityService;
use App\Domains\Benefits\Services\BenefitPlanService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\JobGrade;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BenefitPlanAndEligibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_benefit_plan_creation_and_versioning(): void
    {
        $tenant = Tenant::factory()->create();
        $category = BenefitCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'HEALTH',
            'name' => 'Health Category',
            'category_type' => BenefitCategoryType::HEALTH_INSURANCE->value,
            'is_active' => true,
        ]);

        $provider = BenefitProvider::create([
            'tenant_id' => $tenant->id,
            'provider_code' => 'BCBS',
            'name' => 'Blue Cross Blue Shield',
            'is_active' => true,
        ]);

        $planService = app(BenefitPlanService::class);

        $plan = $planService->createPlan([
            'tenant_id' => $tenant->id,
            'benefit_category_id' => $category->id,
            'benefit_provider_id' => $provider->id,
            'code' => 'BCBS-PPO',
            'name' => 'BCBS Gold PPO',
            'benefit_type' => 'health_insurance',
            'coverage_level' => 'family',
            'employee_cost' => 150.0000,
            'employer_cost' => 500.0000,
            'annual_limit' => 150000.0000,
            'effective_from' => '2026-01-01',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('benefit_plans', ['code' => 'BCBS-PPO', 'version' => 1]);
        $this->assertEquals(1, $plan->versions()->count());

        // Create new version
        $version2 = $planService->createPlanVersion($plan, [
            'effective_from' => '2027-01-01',
            'employee_cost' => 175.0000,
            'employer_cost' => 550.0000,
            'annual_limit' => 200000.0000,
        ]);

        $this->assertEquals(2, $plan->fresh()->version);
        $this->assertEquals(175.0000, (float) $plan->fresh()->employee_cost);
    }

    public function test_benefit_eligibility_rule_evaluation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = \App\Domains\Organization\Models\BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'code' => 'BU-HQ',
            'name' => 'Headquarters',
        ]);
        $deptEng = Department::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'ENG',
            'department_name' => 'Engineering',
        ]);
        $deptSales = Department::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'SALES',
            'department_name' => 'Sales',
        ]);
        $gradeSenior = JobGrade::create([
            'tenant_id' => $tenant->id,
            'grade_code' => 'G-SENIOR',
            'grade_name' => 'Senior Grade',
            'level' => 5,
        ]);

        $empEligible = $this->createEmployee($tenant->id, $company->id, [
            'department_id' => $deptEng->id,
            'job_grade_id' => $gradeSenior->id,
            'joining_date' => '2025-01-01',
        ]);

        $empIneligible = $this->createEmployee($tenant->id, $company->id, [
            'department_id' => $deptSales->id,
            'job_grade_id' => null,
            'joining_date' => '2026-08-01',
        ]);

        $plan = BenefitPlan::create([
            'tenant_id' => $tenant->id,
            'code' => 'EXEC-HEALTH',
            'name' => 'Executive Health Plan',
            'benefit_type' => 'health_insurance',
            'employee_cost' => 100,
            'employer_cost' => 800,
            'effective_from' => '2026-01-01',
            'status' => 'active',
        ]);

        $plan->eligibilityRules()->create([
            'tenant_id' => $tenant->id,
            'rule_name' => 'Engineering Only & Min 6 Months',
            'criteria' => [
                'department_ids' => [$deptEng->id],
                'min_service_months' => 6,
            ],
            'is_active' => true,
        ]);

        $eligibilityService = app(BenefitEligibilityService::class);

        $this->assertTrue($eligibilityService->isEmployeeEligible($empEligible, $plan));
        $this->assertFalse($eligibilityService->isEmployeeEligible($empIneligible, $plan));
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'official_email' => 'alice.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
