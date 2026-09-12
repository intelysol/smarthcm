<?php

namespace Tests\Feature\Expenses;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Enums\AdvanceStatus;
use App\Domains\Expenses\Enums\SettlementType;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Models\TravelAdvance;
use App\Domains\Expenses\Services\ExpenseClaimService;
use App\Domains\Expenses\Services\TravelAdvanceService;
use App\Domains\Expenses\Services\TravelRequestService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelAdvanceAndSettlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_travel_advance_disbursement_claim_offset_and_refund_settlement(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);
        $approver = User::factory()->create(['tenant_id' => $tenant->id]);

        $category = ExpenseCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'MEALS',
            'name' => 'Meals',
            'category_type' => 'meals',
            'is_reimbursable' => true,
        ]);

        $advanceService = app(TravelAdvanceService::class);
        $claimService = app(ExpenseClaimService::class);

        // 1. Request Travel Advance of $1,500
        $advance = $advanceService->requestAdvance($employee, [
            'requested_amount' => 1500.0000,
            'purpose' => 'Advance for London Client Meetings',
            'currency' => 'USD',
        ]);

        $this->assertEquals(AdvanceStatus::REQUESTED->value, $advance->status);

        // 2. Approve Advance
        $approved = $advanceService->approveAdvance($advance, 1500.0000, $approver);
        $this->assertEquals(AdvanceStatus::APPROVED->value, $approved->status);

        // 3. Disburse Advance
        $disb = $advanceService->disburseAdvance($approved, [
            'disbursed_amount' => 1500.0000,
            'payment_method' => 'bank_transfer',
            'finance_reference' => 'WIRE-9921',
        ], $approver);

        $this->assertEquals(AdvanceStatus::DISBURSED->value, $advance->fresh()->status);
        $this->assertEquals(1500.0000, $advance->fresh()->remainingUnsettledAmount());

        // 4. Create an Expense Claim of $1,000 and offset against the $1,500 advance
        $claim = $claimService->createClaim($employee, [
            'title' => 'London Trip Expenses',
            'currency' => 'USD',
            'claim_date' => '2026-10-05',
        ]);

        $claimService->addLine($claim, [
            'expense_category_id' => $category->id,
            'expense_date' => '2026-10-05',
            'description' => 'Dinner with Enterprise Client',
            'original_amount' => 1000.0000,
            'original_currency' => 'USD',
        ]);

        // Settle $1,000 against advance
        $settlement = $advanceService->settleAdvanceAgainstClaim($advance->fresh(), $claim, 1000.0000);

        $this->assertEquals(1000.0000, (float) $settlement->settled_amount);
        $this->assertEquals(500.0000, (float) $settlement->remaining_balance);
        $this->assertEquals(500.0000, $advance->fresh()->remainingUnsettledAmount());
        $this->assertEquals(0.0000, (float) $claim->fresh()->net_reimbursement_amount); // Fully absorbed by advance!

        // 5. Settle the remaining $500 as Employee Refund
        $finalSettlement = $advanceService->settleRemainingWithRefundOrRecovery($advance->fresh(), SettlementType::EMPLOYEE_REFUND->value, 'CASH-DEP-101');

        $this->assertEquals(AdvanceStatus::SETTLED->value, $advance->fresh()->status);
        $this->assertEquals(0.0000, $advance->fresh()->remainingUnsettledAmount());
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Benjamin',
            'last_name' => 'Franklin',
            'official_email' => 'ben.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
