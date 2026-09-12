<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Enums\InterestMethod;
use App\Domains\Benefits\Enums\LoanApplicationStatus;
use App\Domains\Benefits\Enums\LoanProductType;
use App\Domains\Benefits\Models\LoanProduct;
use App\Domains\Benefits\Services\LoanApplicationService;
use App\Domains\Benefits\Services\LoanDisbursementService;
use App\Domains\Benefits\Services\LoanProductService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanApplicationDisbursementAndScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_loan_application_approval_agreement_and_amortization_schedule(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id, [
            'joining_date' => now()->subYears(2)->toDateString(),
        ]);
        $approver = User::factory()->create(['tenant_id' => $tenant->id]);

        $productService = app(LoanProductService::class);
        $product = $productService->createProduct([
            'tenant_id' => $tenant->id,
            'code' => 'PERSONAL-12M',
            'name' => '12-Month Personal Loan',
            'loan_type' => LoanProductType::PERSONAL->value,
            'minimum_amount' => 500,
            'maximum_amount' => 10000,
            'max_installments' => 12,
            'interest_rate_annual' => 6.0000, // 6% annual
            'interest_method' => InterestMethod::FLAT_RATE->value,
            'min_service_months' => 6,
        ]);

        $appService = app(LoanApplicationService::class);

        // 1. Submit Application for $6,000 for 12 months
        $application = $appService->apply($employee, $product, [
            'requested_amount' => 6000.0000,
            'requested_tenure_months' => 12,
            'purpose' => 'Home renovation',
        ]);

        $this->assertEquals(LoanApplicationStatus::SUBMITTED->value, $application->status);

        // 2. Approve Application & Auto-generate Agreement
        $approvedApp = $appService->approve($application, [
            'approved_amount' => 6000.0000,
            'approved_tenure_months' => 12,
            'interest_rate' => 6.0000,
        ], $approver);

        $this->assertEquals(LoanApplicationStatus::APPROVED->value, $approvedApp->status);
        $this->assertNotNull($approvedApp->agreement);
        $this->assertEquals(6000.0000, (float) $approvedApp->agreement->principal_amount);

        // 3. Disburse Loan & Generate 12 Installments
        $disburseService = app(LoanDisbursementService::class);
        $disbursement = $disburseService->disburse($approvedApp, [
            'disbursed_amount' => 6000.0000,
            'disbursement_date' => '2026-09-01',
            'disbursement_method' => 'bank_transfer',
        ], $approver);

        $this->assertEquals(LoanApplicationStatus::DISBURSED->value, $approvedApp->fresh()->status);

        $activeSchedule = $approvedApp->activeSchedule;
        $this->assertNotNull($activeSchedule);
        $this->assertEquals(12, $activeSchedule->installments()->count());

        // For flat rate 6% on $6,000 for 12 mos:
        // Total interest = $360.00
        // Monthly installment = $500 principal + $30 interest = $530.00
        $firstInstallment = $activeSchedule->installments()->where('installment_number', 1)->first();
        $this->assertEquals(500.0000, (float) $firstInstallment->principal_amount);
        $this->assertEquals(30.0000, (float) $firstInstallment->interest_amount);
        $this->assertEquals(530.0000, (float) $firstInstallment->total_installment);
        $this->assertEquals(6360.0000, (float) $activeSchedule->total_payable);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Henry',
            'last_name' => 'Ford',
            'official_email' => 'henry.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
