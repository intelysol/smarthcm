<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Enums\EnrollmentStatus;
use App\Domains\Benefits\Models\BenefitCoverage;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Services\BenefitCoverageService;
use App\Domains\Benefits\Services\BenefitElectionService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Models\EmployeeFamilyMember;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BenefitEnrollmentWizardAndElectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_multi_step_election_flow_with_dependents_and_beneficiary_validation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $hrAdmin = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-777',
            'employee_number' => '7777',
            'first_name' => 'Michael',
            'last_name' => 'Scott',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        // Create family members in Epic 2.32
        $spouse = EmployeeFamilyMember::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'name' => 'Holly Flax',
            'relationship' => 'spouse',
            'date_of_birth' => '1980-05-12',
        ]);

        $child = EmployeeFamilyMember::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'name' => 'Astrid Scott',
            'relationship' => 'child',
            'date_of_birth' => '2015-08-20',
        ]);

        $plan = BenefitPlan::create([
            'tenant_id' => $tenant->id,
            'code' => 'LIFE-2027',
            'name' => 'Group Term Life Insurance',
            'benefit_type' => 'life_insurance',
            'employee_cost' => 20.0000,
            'employer_cost' => 80.0000,
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'requires_beneficiary' => true,
        ]);

        $coverageFamily = BenefitCoverage::create([
            'tenant_id' => $tenant->id,
            'benefit_plan_id' => $plan->id,
            'code' => 'EMP_SPOUSE',
            'name' => 'employee_plus_spouse',
            'coverage_multiplier' => 1.5,
            'employee_cost_factor' => 1.5,
            'employer_cost_factor' => 1.5,
            'max_dependents' => 2,
        ]);

        $electionService = app(BenefitElectionService::class);

        // 1. Validation failure: Beneficiary allocations do not sum to 100%
        try {
            $electionService->elect($employee, $plan, [
                'benefit_coverage_id' => $coverageFamily->id,
                'dependents' => [
                    ['family_member_id' => $spouse->id, 'name' => $spouse->name, 'relationship' => $spouse->relationship],
                ],
                'beneficiaries' => [
                    ['name' => 'Holly Flax', 'relationship' => 'spouse', 'percentage_allocation' => 60.00, 'is_primary' => true],
                    ['name' => 'Astrid Scott', 'relationship' => 'child', 'percentage_allocation' => 30.00, 'is_primary' => true],
                    // Total = 90% -> Must fail validation!
                ],
            ]);
            $this->fail('Expected ValidationException due to beneficiary sum != 100%');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('beneficiaries', $e->errors());
        }

        // 2. Successful Election: Beneficiaries sum to exactly 100%
        $election = $electionService->elect($employee, $plan, [
            'benefit_coverage_id' => $coverageFamily->id,
            'dependents' => [
                ['family_member_id' => $spouse->id, 'name' => $spouse->name, 'relationship' => $spouse->relationship],
                ['family_member_id' => $child->id, 'name' => $child->name, 'relationship' => $child->relationship],
            ],
            'beneficiaries' => [
                ['name' => 'Holly Flax', 'relationship' => 'spouse', 'percentage_allocation' => 70.00, 'is_primary' => true],
                ['name' => 'Astrid Scott', 'relationship' => 'child', 'percentage_allocation' => 30.00, 'is_primary' => true],
            ],
            'election_date' => '2026-11-15',
            'effective_date' => '2027-01-01',
        ], $employee->user ?? null);

        $this->assertEquals('elected', $election->status);
        $this->assertEquals(30.00, (float) $election->employee_cost_estimated); // 20 * 1.5
        $this->assertEquals(120.00, (float) $election->employer_cost_estimated); // 80 * 1.5

        // 3. Confirm Election
        $election = $electionService->confirmElection($election);
        $this->assertEquals('confirmed', $election->status);
        $this->assertNotNull($election->confirmed_at);

        // 4. HR Approval activates Enrollment and creates BenefitDependent & BenefitBeneficiary records
        $enrollment = $electionService->approveElection($election, $hrAdmin);

        $this->assertEquals('approved', $election->fresh()->status);
        $this->assertEquals(EnrollmentStatus::APPROVED->value, $enrollment->status);
        $this->assertEquals(2, $enrollment->dependents()->count());
        $this->assertEquals(2, $enrollment->beneficiaries()->count());
    }
}
