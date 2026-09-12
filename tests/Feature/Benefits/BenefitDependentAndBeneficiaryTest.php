<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Models\BenefitBeneficiary;
use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Services\BeneficiaryService;
use App\Domains\Benefits\Services\BenefitDependentService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BenefitDependentAndBeneficiaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_dependent_linkage_and_beneficiary_allocation_validation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $plan = BenefitPlan::create([
            'tenant_id' => $tenant->id,
            'code' => 'TERM-LIFE-100K',
            'name' => '100K Group Term Life',
            'benefit_type' => 'life_insurance',
            'employee_cost' => 10,
            'employer_cost' => 40,
            'effective_from' => '2026-01-01',
            'status' => 'active',
        ]);

        $enrollment = BenefitEnrollment::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'benefit_plan_id' => $plan->id,
            'effective_from' => '2026-01-01',
            'status' => 'approved',
        ]);

        // 1. Add Dependent
        $depService = app(BenefitDependentService::class);
        $dependent = $depService->addDependent($enrollment, [
            'name' => 'Sarah Connor',
            'relationship' => 'spouse',
            'date_of_birth' => '1990-05-15',
        ]);

        $this->assertDatabaseHas('benefit_dependents', [
            'benefit_enrollment_id' => $enrollment->id,
            'name' => 'Sarah Connor',
            'relationship' => 'spouse',
        ]);

        // 2. Beneficiary Nomination
        $beneficiaryService = app(BeneficiaryService::class);

        // Nominate Primary 1 (60%)
        $b1 = $beneficiaryService->addBeneficiary($employee, [
            'name' => 'Sarah Connor',
            'relationship' => 'spouse',
            'percentage_allocation' => 60.00,
            'is_primary' => true,
        ], $enrollment);

        // Nominate Primary 2 (40%) -> Total 100%
        $b2 = $beneficiaryService->addBeneficiary($employee, [
            'name' => 'John Connor',
            'relationship' => 'child',
            'percentage_allocation' => 40.00,
            'is_primary' => true,
        ], $enrollment);

        $this->assertEquals(60.00, (float) $b1->percentage_allocation);
        $this->assertEquals(40.00, (float) $b2->percentage_allocation);

        // 3. Attempting to add extra 10% exceeding 100% must throw ValidationException
        $this->expectException(ValidationException::class);
        $beneficiaryService->addBeneficiary($employee, [
            'name' => 'Other Relative',
            'relationship' => 'sibling',
            'percentage_allocation' => 10.00,
            'is_primary' => true,
        ], $enrollment);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Sarah',
            'last_name' => 'Connor',
            'official_email' => 'sarah.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
