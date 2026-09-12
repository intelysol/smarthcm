<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Enums\RetirementPlanType;
use App\Domains\Benefits\Models\RetirementAccount;
use App\Domains\Benefits\Models\RetirementPlan;
use App\Domains\Benefits\Services\RetirementAccountService;
use App\Domains\Benefits\Services\RetirementContributionService;
use App\Domains\Benefits\Services\RetirementEnrollmentService;
use App\Domains\Benefits\Services\RetirementPlanService;
use App\Domains\Benefits\Services\RetirementVestingService;
use App\Domains\Benefits\Services\RetirementWithdrawalService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetirementVestingAndLedgerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_retirement_ledger_consistency_vesting_and_withdrawal(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id, [
            'joining_date' => now()->subYears(3)->subDays(10)->toDateString(), // 3+ completed years
        ]);
        $approver = User::factory()->create(['tenant_id' => $tenant->id]);

        $planService = app(RetirementPlanService::class);
        $plan = $planService->createPlan([
            'tenant_id' => $tenant->id,
            'plan_code' => 'VESTED-PENSION',
            'name' => 'Graded Vesting Pension Fund',
            'plan_type' => RetirementPlanType::PENSION->value,
            'default_employee_rate' => 5.0000,
            'max_employer_contribution_rate' => 5.0000,
            'vesting_type' => 'graded',
        ]);

        // Graded Vesting: Year 1 = 20%, Year 2 = 40%, Year 3 = 60%, Year 4 = 80%, Year 5 = 100%
        $planService->configureVesting($plan, [
            ['completed_years' => 1, 'vesting_percentage' => 20.00],
            ['completed_years' => 2, 'vesting_percentage' => 40.00],
            ['completed_years' => 3, 'vesting_percentage' => 60.00],
            ['completed_years' => 4, 'vesting_percentage' => 80.00],
            ['completed_years' => 5, 'vesting_percentage' => 100.00],
        ]);

        $enrollmentService = app(RetirementEnrollmentService::class);
        $enrollmentService->enrollEmployee($employee, $plan);

        $contribService = app(RetirementContributionService::class);
        // Post contribution on $20,000 basic salary -> $1,000 Emp + $1,000 Empr = $2,000 total
        $contribService->calculateAndPostMonthlyContribution($employee, $plan, 20000.0000);

        $account = RetirementAccount::where('employee_id', $employee->id)->first();

        // Vesting calculation:
        // Employee portion = $1,000 (100% vested)
        // Employer portion = $1,000 (60% vested for 3 years service = $600)
        // Total vested = $1,600
        $vestingService = app(RetirementVestingService::class);
        $vestedBalance = $vestingService->calculateVestedBalance($account);
        $this->assertEquals(1600.0000, $vestedBalance);

        // Withdrawal Request for $1,200 (within $1,600 vested balance)
        $withdrawalService = app(RetirementWithdrawalService::class);
        $withdrawal = $withdrawalService->requestWithdrawal($account, [
            'withdrawal_type' => 'partial',
            'requested_amount' => 1200.0000,
            'reason' => 'Home repair emergency',
        ]);

        $this->assertEquals('submitted', $withdrawal->status);

        // Approve and Disburse ($1,200 with 10% tax withheld = $120 tax, $1,080 net)
        $disbursed = $withdrawalService->approveAndDisburse($withdrawal, 1200.0000, 120.0000, $approver);
        $this->assertEquals('disbursed', $disbursed->status);
        $this->assertEquals(1080.0000, (float) $disbursed->net_disbursed_amount);

        // Check account statement & ledger consistency
        $accountService = app(RetirementAccountService::class);
        $statement = $accountService->getAccountStatement($account->fresh());

        $this->assertTrue($statement['is_ledger_consistent']);
        $this->assertEquals(800.0000, (float) $statement['current_balance']);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Frank',
            'last_name' => 'Sinatra',
            'official_email' => 'frank.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
