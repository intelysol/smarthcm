<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Enums\ClaimStatus;
use App\Domains\Benefits\Models\BenefitProvider;
use App\Domains\Benefits\Models\InsurancePolicy;
use App\Domains\Benefits\Services\BenefitsAuthorizationService;
use App\Domains\Benefits\Services\InsuranceClaimService;
use App\Domains\Benefits\Services\InsurancePolicyService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InsurancePolicyAndMedicalClaimTest extends TestCase
{
    use RefreshDatabase;

    public function test_insurance_claim_workflow_itemized_lines_and_medical_privacy(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);
        $approver = User::factory()->create(['tenant_id' => $tenant->id]);

        $provider = BenefitProvider::create([
            'tenant_id' => $tenant->id,
            'provider_code' => 'CIGNA',
            'name' => 'Cigna Global Health',
        ]);

        $policyService = app(InsurancePolicyService::class);
        $policy = $policyService->createPolicy($provider, [
            'policy_number' => 'GRP-POL-9921',
            'policy_name' => 'Executive Corporate Medical Coverage',
            'insurance_type' => 'health',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'total_premium' => 50000.0000,
        ]);

        $claimService = app(InsuranceClaimService::class);

        // Submit Claim with itemized lines
        $claim = $claimService->submitClaim($employee, [
            'claim_type' => 'inpatient',
            'incident_date' => '2026-06-15',
            'service_provider_name' => 'St. Jude Memorial Hospital',
            'claimed_amount' => 4500.0000,
            'is_sensitive_medical' => true,
            'diagnosis_details' => 'Emergency appendectomy surgical procedure and post-op medication.',
            'lines' => [
                ['item_description' => 'Surgical Theater & Anesthesia', 'claimed_amount' => 3000.0000, 'invoice_number' => 'INV-881'],
                ['item_description' => 'Post-op Ward & Medications', 'claimed_amount' => 1500.0000, 'invoice_number' => 'INV-882'],
            ],
        ], $policy);

        $this->assertEquals(ClaimStatus::SUBMITTED->value, $claim->status);
        $this->assertEquals(2, $claim->lines()->count());

        // Approve Claim
        $approved = $claimService->approveClaim($claim, 4200.0000, $approver);
        $this->assertEquals(ClaimStatus::APPROVED->value, $approved->status);
        $this->assertEquals(4200.0000, (float) $approved->approved_amount);

        // Security check
        $authService = app(BenefitsAuthorizationService::class);
        $this->assertTrue($authService->canViewSensitiveMedicalClaim($approver, $claim));
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'David',
            'last_name' => 'Miller',
            'official_email' => 'david.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
