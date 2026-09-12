<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Enums\InterestMethod;
use App\Domains\Benefits\Enums\LoanProductType;
use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Models\LoanProduct;
use App\Domains\Benefits\Services\BenefitsAnalyticsService;
use App\Domains\Benefits\Services\BenefitsPayrollIntegrationService;
use App\Domains\Benefits\Services\LoanApplicationService;
use App\Domains\Benefits\Services\LoanDisbursementService;
use App\Domains\Benefits\Services\LoanProductService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Payroll\Enums\PayFrequency;
use App\Domains\Payroll\Models\PayrollCalendar;
use App\Domains\Payroll\Models\PayrollLegalEntity;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BenefitsPayrollIntegrationAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_payroll_input_line_sync_and_total_employment_cost_analytics(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id, [
            'joining_date' => '2024-01-01',
        ]);
        $approver = User::factory()->create(['tenant_id' => $tenant->id]);

        $legalEntity = PayrollLegalEntity::create([
            'tenant_id' => $tenant->id,
            'code' => 'CORP-US',
            'name' => 'Corporate US Inc',
            'country' => 'USA',
            'base_currency' => 'USD',
        ]);

        $calendar = PayrollCalendar::create([
            'tenant_id' => $tenant->id,
            'payroll_legal_entity_id' => $legalEntity->id,
            'code' => 'MONTHLY-CAL',
            'name' => 'Monthly Standard Calendar',
            'frequency' => PayFrequency::MONTHLY->value,
            'period_pattern' => 'calendar_month',
        ]);

        $period = PayrollPeriod::create([
            'tenant_id' => $tenant->id,
            'payroll_calendar_id' => $calendar->id,
            'payroll_legal_entity_id' => $legalEntity->id,
            'period_name' => 'September 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'cutoff_date' => '2026-09-25',
            'status' => 'open',
        ]);

        // 1. Benefit Enrollment ($150 employee deduction, $450 employer cost)
        $plan = BenefitPlan::create([
            'tenant_id' => $tenant->id,
            'code' => 'EXEC-HLTH',
            'name' => 'Executive Health',
            'benefit_type' => 'health_insurance',
            'employee_cost' => 150.0000,
            'employer_cost' => 450.0000,
            'effective_from' => '2026-01-01',
            'status' => 'active',
        ]);

        BenefitEnrollment::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'benefit_plan_id' => $plan->id,
            'employee_contribution' => 150.0000,
            'employer_contribution' => 450.0000,
            'effective_from' => '2026-01-01',
            'status' => 'approved',
        ]);

        // 2. Loan Installment ($300 monthly deduction due in September)
        $productService = app(LoanProductService::class);
        $prod = $productService->createProduct([
            'tenant_id' => $tenant->id,
            'code' => 'LOAN-CORP',
            'name' => 'Corporate Loan',
            'loan_type' => LoanProductType::PERSONAL->value,
            'minimum_amount' => 100,
            'maximum_amount' => 5000,
            'max_installments' => 12,
            'interest_rate_annual' => 0.0000,
            'interest_method' => InterestMethod::ZERO_INTEREST->value,
        ]);

        $appService = app(LoanApplicationService::class);
        $loanApp = $appService->apply($employee, $prod, ['requested_amount' => 1200.0000, 'requested_tenure_months' => 4]);
        $appApproved = $appService->approve($loanApp, [], $approver);

        $disburseService = app(LoanDisbursementService::class);
        $disburseService->disburse($appApproved, ['disbursement_date' => '2026-08-31'], $approver);

        // 3. Sync to Payroll Input Lines
        $payrollIntegrationService = app(BenefitsPayrollIntegrationService::class);
        $lines = $payrollIntegrationService->syncPayrollInputsForPeriod($period, $employee);

        $this->assertNotEmpty($lines);
        $this->assertDatabaseHas('payroll_input_lines', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'source_module' => 'benefits',
            'input_type' => 'benefit_deduction',
            'amount' => 150.0000,
        ]);
        $this->assertDatabaseHas('payroll_input_lines', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'source_module' => 'loans',
            'input_type' => 'loan_installment',
            'amount' => 300.0000,
        ]);

        // 4. Total Employment Cost Analytics Calculation
        $analyticsService = app(BenefitsAnalyticsService::class);
        $costResult = $analyticsService->calculateTotalEmploymentCost($employee, 8000.0000);

        $this->assertEquals(8000.0000, (float) $costResult['basic_salary']);
        $this->assertEquals(450.0000, (float) $costResult['employer_insurance_cost']);
        $this->assertGreaterThanOrEqual(8450.0000, (float) $costResult['total_employment_cost']);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Jack',
            'last_name' => 'Nicholson',
            'official_email' => 'jack.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
