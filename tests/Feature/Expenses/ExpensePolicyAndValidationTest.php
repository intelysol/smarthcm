<?php

namespace Tests\Feature\Expenses;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Models\ExpensePolicy;
use App\Domains\Expenses\Services\ExpenseClaimService;
use App\Domains\Expenses\Services\ExpensePolicyService;
use App\Domains\Expenses\Services\ReceiptService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpensePolicyAndValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_policy_meal_cap_receipt_threshold_and_override(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);
        $approver = User::factory()->create(['tenant_id' => $tenant->id]);

        $mealCategory = ExpenseCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'MEALS',
            'name' => 'Meals & Dining',
            'category_type' => 'meals',
            'receipt_threshold' => 100.0000,
            'receipt_required' => false,
        ]);

        $policyService = app(ExpensePolicyService::class);
        $policy = $policyService->createPolicy([
            'tenant_id' => $tenant->id,
            'name' => 'Strict Travel Policy',
            'daily_meal_limit' => 200.0000, // $200 daily meal cap
            'daily_hotel_limit' => 500.0000,
            'receipt_required_threshold' => 100.0000,
        ]);

        $policyService->assignPolicy($policy, 'employee', $employee->id, 1);

        $claimService = app(ExpenseClaimService::class);
        $receiptService = app(ReceiptService::class);

        $claim = $claimService->createClaim($employee, [
            'title' => 'Client Lunch & Dinner',
            'claim_date' => '2026-10-02',
        ]);

        // 1. Add meal expense line of $350 (exceeds $200 cap -> reduced to $200)
        $line1 = $claimService->addLine($claim, [
            'expense_category_id' => $mealCategory->id,
            'expense_date' => '2026-10-02',
            'description' => 'VIP Dinner with Executive Stakeholders',
            'original_amount' => 350.0000,
            'original_currency' => 'USD',
        ]);

        $this->assertEquals('excess_reduced', $line1->policy_status);
        $this->assertEquals(200.0000, (float) $line1->approved_base_amount);
        $this->assertEquals(1, $line1->policyResults()->where('rule_type', 'daily_meal_limit')->count());

        // 2. Override policy violation by authorized manager
        $overridden = $claimService->overridePolicyViolation($line1, $approver, 'Authorized exception by VP Sales for key enterprise deal.');
        $this->assertEquals('compliant', $overridden->policy_status);
        $this->assertEquals(350.0000, (float) $overridden->approved_base_amount);
        $this->assertEquals(350.0000, (float) $claim->fresh()->approved_total);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Clara',
            'last_name' => 'Barton',
            'official_email' => 'clara.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
