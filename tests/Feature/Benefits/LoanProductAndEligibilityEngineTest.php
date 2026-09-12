<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Enums\InterestMethod;
use App\Domains\Benefits\Enums\LoanProductType;
use App\Domains\Benefits\Models\LoanProduct;
use App\Domains\Benefits\Services\LoanEligibilityService;
use App\Domains\Benefits\Services\LoanProductService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanProductAndEligibilityEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_loan_product_configuration_and_eligibility_constraints(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $empExperienced = $this->createEmployee($tenant->id, $company->id, [
            'joining_date' => now()->subMonths(12)->toDateString(),
        ]);
        $empProbation = $this->createEmployee($tenant->id, $company->id, [
            'joining_date' => now()->subMonths(2)->toDateString(),
        ]);

        $productService = app(LoanProductService::class);
        $product = $productService->createProduct([
            'tenant_id' => $tenant->id,
            'code' => 'EDU-LOAN',
            'name' => 'Higher Education Assistance Loan',
            'loan_type' => LoanProductType::EDUCATION->value,
            'minimum_amount' => 1000.0000,
            'maximum_amount' => 15000.0000,
            'max_installments' => 36,
            'interest_rate_annual' => 4.0000,
            'interest_method' => InterestMethod::REDUCING_BALANCE->value,
            'min_service_months' => 6,
        ]);

        $eligibilityService = app(LoanEligibilityService::class);

        // 1. Experienced employee requesting $5,000 -> Eligible
        $eval1 = $eligibilityService->evaluateEligibility($empExperienced, $product, 5000.0000);
        $this->assertTrue($eval1['is_eligible']);

        // 2. Probation employee requesting $5,000 -> Ineligible (min service)
        $eval2 = $eligibilityService->evaluateEligibility($empProbation, $product, 5000.0000);
        $this->assertFalse($eval2['is_eligible']);
        $this->assertStringContainsString('below required minimum', $eval2['errors'][0]);

        // 3. Experienced employee requesting $25,000 (exceeds $15,000 max) -> Ineligible
        $eval3 = $eligibilityService->evaluateEligibility($empExperienced, $product, 25000.0000);
        $this->assertFalse($eval3['is_eligible']);
        $this->assertStringContainsString('exceeds product maximum', $eval3['errors'][0]);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'George',
            'last_name' => 'Clooney',
            'official_email' => 'george.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
