<?php

namespace Tests\Feature\Expenses;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Enums\ExpenseClaimStatus;
use App\Domains\Expenses\Enums\ReimbursementMethod;
use App\Domains\Expenses\Enums\ReimbursementStatus;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Services\ExpenseClaimService;
use App\Domains\Expenses\Services\ExpenseReimbursementService;
use App\Domains\Organization\Models\Company;
use App\Domains\Payroll\Enums\PayFrequency;
use App\Domains\Payroll\Models\PayrollCalendar;
use App\Domains\Payroll\Models\PayrollLegalEntity;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseReimbursementAndPayrollIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reimbursement_batching_payroll_sync_and_payment_completion(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);
        $financeApprover = User::factory()->create(['tenant_id' => $tenant->id]);

        $legalEntity = PayrollLegalEntity::create([
            'tenant_id' => $tenant->id,
            'code' => 'LE-EXP-01',
            'name' => 'Corporate Legal Entity',
            'country' => 'USA',
            'base_currency' => 'USD',
        ]);

        $calendar = PayrollCalendar::create([
            'tenant_id' => $tenant->id,
            'payroll_legal_entity_id' => $legalEntity->id,
            'code' => 'M-EXP',
            'name' => 'Monthly Expense Calendar',
            'frequency' => PayFrequency::MONTHLY->value,
            'period_pattern' => 'calendar_month',
        ]);

        $period = PayrollPeriod::create([
            'tenant_id' => $tenant->id,
            'payroll_calendar_id' => $calendar->id,
            'payroll_legal_entity_id' => $legalEntity->id,
            'period_name' => 'October 2026',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'cutoff_date' => '2026-10-25',
            'status' => 'open',
        ]);

        $category = ExpenseCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'MEALS',
            'name' => 'Team Meals',
            'category_type' => 'meals',
        ]);

        $claimService = app(ExpenseClaimService::class);
        $claim = $claimService->createClaim($employee, ['title' => 'Sprint Planning Lunches']);
        $claimService->addLine($claim, [
            'expense_category_id' => $category->id,
            'expense_date' => '2026-10-05',
            'description' => 'Team lunch catering',
            'original_amount' => 600.0000,
        ]);

        $claimService->submitClaim($claim);
        $claimService->approveByManager($claim, $financeApprover);
        $claimService->approveByFinance($claim, $financeApprover);

        // 1. Create Reimbursement Batch via Payroll
        $reimbService = app(ExpenseReimbursementService::class);
        $reimbursement = $reimbService->createReimbursement(
            $employee,
            [$claim],
            ReimbursementMethod::PAYROLL->value,
            $period
        );

        $this->assertEquals(600.0000, (float) $reimbursement->total_reimbursement_amount);
        $this->assertEquals(ReimbursementStatus::PENDING->value, $reimbursement->status);

        // 2. Approve Reimbursement
        $approved = $reimbService->approveReimbursement($reimbursement, $financeApprover);
        $this->assertEquals(ReimbursementStatus::APPROVED->value, $approved->status);

        // 3. Sync to Payroll Input Lines
        $payrollLine = $reimbService->syncToPayrollPeriod($approved, $period);
        $this->assertNotNull($payrollLine);
        $this->assertDatabaseHas('payroll_input_lines', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'source_module' => 'expenses',
            'amount' => 600.0000,
        ]);

        // 4. Mark as Paid & Verify Period Lock on Claim
        $paid = $reimbService->markAsPaid($approved, ['payment_reference' => 'PAYROLL-OCT2026'], $financeApprover);
        $this->assertEquals(ReimbursementStatus::PAID->value, $paid->status);
        $this->assertEquals(ExpenseClaimStatus::PAID->value, $claim->fresh()->status);
        $this->assertTrue($claim->fresh()->is_period_locked);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Isaac',
            'last_name' => 'Newton',
            'official_email' => 'isaac.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
