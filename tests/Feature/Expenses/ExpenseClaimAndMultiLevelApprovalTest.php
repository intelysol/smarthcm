<?php

namespace Tests\Feature\Expenses;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Enums\ExpenseClaimStatus;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Services\ExpenseClaimService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExpenseClaimAndMultiLevelApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_claim_multi_level_approval_and_period_lock(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);
        $financeUser = User::factory()->create(['tenant_id' => $tenant->id]);

        $taxiCategory = ExpenseCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'TAXI',
            'name' => 'Taxi & Rideshare',
            'category_type' => 'transportation',
        ]);

        $claimService = app(ExpenseClaimService::class);

        // 1. Create and add lines
        $claim = $claimService->createClaim($employee, ['title' => 'Weekly Commute']);
        $claimService->addLine($claim, [
            'expense_category_id' => $taxiCategory->id,
            'expense_date' => '2026-09-10',
            'description' => 'Airport Taxi',
            'original_amount' => 75.0000,
        ]);
        $claimService->addLine($claim, [
            'expense_category_id' => $taxiCategory->id,
            'expense_date' => '2026-09-11',
            'description' => 'Client Site Taxi',
            'original_amount' => 45.0000,
        ]);

        $this->assertEquals(ExpenseClaimStatus::DRAFT->value, $claim->status);
        $this->assertEquals(120.0000, (float) $claim->fresh()->claimed_total);

        // 2. Submit Claim
        $submitted = $claimService->submitClaim($claim);
        $this->assertEquals(ExpenseClaimStatus::SUBMITTED->value, $submitted->status);

        // 3. Manager Approval -> advances to Finance Review
        $mgrApproved = $claimService->approveByManager($submitted, $manager);
        $this->assertEquals(ExpenseClaimStatus::FINANCE_REVIEW->value, $mgrApproved->status);
        $this->assertEquals($manager->id, $mgrApproved->approved_by);

        // 4. Finance Approval -> approved
        $finApproved = $claimService->approveByFinance($mgrApproved, $financeUser);
        $this->assertEquals(ExpenseClaimStatus::APPROVED->value, $finApproved->status);
        $this->assertEquals($financeUser->id, $finApproved->finance_reviewed_by);

        // 5. Test Period Lock
        $finApproved->update(['is_period_locked' => true]);

        $this->expectException(ValidationException::class);
        $claimService->addLine($finApproved, [
            'expense_category_id' => $taxiCategory->id,
            'expense_date' => '2026-09-12',
            'description' => 'Late Addition',
            'original_amount' => 30.0000,
        ]);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Fiona',
            'last_name' => 'Apple',
            'official_email' => 'fiona.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
