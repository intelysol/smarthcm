<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Enums\InterestMethod;
use App\Domains\Benefits\Enums\LoanInstallmentStatus;
use App\Domains\Benefits\Enums\LoanProductType;
use App\Domains\Benefits\Models\LoanProduct;
use App\Domains\Benefits\Services\LoanApplicationService;
use App\Domains\Benefits\Services\LoanDisbursementService;
use App\Domains\Benefits\Services\LoanProductService;
use App\Domains\Benefits\Services\LoanRepaymentService;
use App\Domains\Benefits\Services\LoanRestructureService;
use App\Domains\Benefits\Services\LoanSettlementService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanRepaymentRestructureAndSettlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_loan_repayments_versioned_restructuring_and_early_settlement(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);
        $approver = User::factory()->create(['tenant_id' => $tenant->id]);

        $productService = app(LoanProductService::class);
        $product = $productService->createProduct([
            'tenant_id' => $tenant->id,
            'code' => 'EMERGENCY-0',
            'name' => 'Zero Interest Emergency Loan',
            'loan_type' => LoanProductType::EMERGENCY->value,
            'minimum_amount' => 100,
            'maximum_amount' => 5000,
            'max_installments' => 6,
            'interest_rate_annual' => 0.0000,
            'interest_method' => InterestMethod::ZERO_INTEREST->value,
        ]);

        $appService = app(LoanApplicationService::class);
        $app = $appService->apply($employee, $product, [
            'requested_amount' => 3000.0000,
            'requested_tenure_months' => 6,
        ]);

        $approved = $appService->approve($app, [], $approver);

        $disburseService = app(LoanDisbursementService::class);
        $disburseService->disburse($approved, [], $approver);

        $schedule = $approved->fresh()->activeSchedule;
        $inst1 = $schedule->installments()->where('installment_number', 1)->first();

        // 1. Post Repayment for Month 1 ($500)
        $repayService = app(LoanRepaymentService::class);
        $paidInst1 = $repayService->recordPayrollRepayment($inst1, 500.0000);

        $this->assertEquals(LoanInstallmentStatus::DEDUCTED_PAYROLL->value, $paidInst1->status);
        $this->assertEquals(2500.0000, (float) $schedule->fresh()->remaining_balance);

        // 2. Loan Restructuring: Extend remaining $2,500 over 10 months
        $restructureService = app(LoanRestructureService::class);
        $restructure = $restructureService->restructure(
            $approved->fresh(),
            10,
            0.0000,
            'Medical hardship requested extended terms',
            $approver
        );

        $this->assertEquals(1, $restructure->previous_schedule_version);
        $this->assertEquals(2, $restructure->new_schedule_version);

        $newSchedule = $approved->fresh()->activeSchedule;
        $this->assertEquals(2, $newSchedule->schedule_version);
        $this->assertEquals(10, $newSchedule->installments()->count());
        $this->assertEquals(250.0000, (float) $newSchedule->installments()->first()->total_installment);

        // 3. Early Settlement of remaining balance ($2,500 with $100 rebate = $2,400 final payoff)
        $settleService = app(LoanSettlementService::class);
        $settlement = $settleService->earlySettlement($approved->fresh(), 100.0000, 'bank_wire', $approver);

        $this->assertEquals(2400.0000, (float) $settlement->final_settlement_amount);
        $this->assertEquals('closed', $approved->fresh()->status);
        $this->assertEquals(0.0000, (float) $newSchedule->fresh()->remaining_balance);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Ian',
            'last_name' => 'McKellen',
            'official_email' => 'ian.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
