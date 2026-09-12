<?php

namespace Tests\Feature\Expenses;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Models\TravelAdvance;
use App\Domains\Expenses\Models\TravelRequest;
use App\Domains\Expenses\Services\ExpenseAccountingService;
use App\Domains\Expenses\Services\ExpenseClaimService;
use App\Domains\Expenses\Services\ExpenseReimbursementService;
use App\Domains\Expenses\Services\TravelAdvanceService;
use App\Domains\Expenses\Services\TravelRequestService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExpenseAuditAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_audit_logging_and_hash_chain_integrity_for_expenses_lifecycle(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);
        $financeUser = User::factory()->create(['tenant_id' => $tenant->id]);

        $category = ExpenseCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'HOTEL',
            'name' => 'Hotel Lodging',
            'category_type' => 'lodging',
            'is_reimbursable' => true,
        ]);

        $auditService = app(AuditService::class);
        $claimService = app(ExpenseClaimService::class);
        $travelService = app(TravelRequestService::class);
        $advanceService = app(TravelAdvanceService::class);
        $reimbursementService = app(ExpenseReimbursementService::class);
        $accountingService = app(ExpenseAccountingService::class);

        // 1. Travel Request & Audit
        Carbon::setTestNow('2026-11-01 09:00:00');
        $travel = $travelService->createTravelRequest($employee, [
            'destination' => 'Zurich, Switzerland',
            'purpose' => 'European Partner Summit',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-05',
            'estimated_cost' => 2500.0000,
        ]);
        Carbon::setTestNow('2026-11-01 09:05:00');
        $travelService->submitTravelRequest($travel);
        Carbon::setTestNow('2026-11-01 10:00:00');
        $travelService->approveTravelRequest($travel, $manager, 2500.0000);

        // 2. Travel Advance & Audit
        Carbon::setTestNow('2026-11-01 11:00:00');
        $advance = $advanceService->requestAdvance($employee, [
            'requested_amount' => 1000.0000,
            'purpose' => 'Zurich trip advance',
        ]);
        Carbon::setTestNow('2026-11-01 11:30:00');
        $advanceService->approveAdvance($advance, 1000.0000, $manager);
        Carbon::setTestNow('2026-11-01 14:00:00');
        $advanceService->disburseAdvance($advance, [
            'disbursed_amount' => 1000.0000,
            'payment_method' => 'bank_transfer',
        ], $financeUser);

        // 3. Expense Claim & Audit
        Carbon::setTestNow('2026-11-04 18:00:00');
        $claim = $claimService->createClaim($employee, [
            'title' => 'Zurich Summit Expenses',
            'claim_date' => '2026-11-04',
        ]);

        Carbon::setTestNow('2026-11-04 18:05:00');
        $line = $claimService->addLine($claim, [
            'expense_category_id' => $category->id,
            'expense_date' => '2026-11-04',
            'description' => 'Hotel Zurich Central',
            'original_amount' => 1400.0000,
        ]);

        Carbon::setTestNow('2026-11-04 18:10:00');
        $claimService->submitClaim($claim);
        Carbon::setTestNow('2026-11-04 19:00:00');
        $claimService->approveByManager($claim, $manager);
        Carbon::setTestNow('2026-11-05 09:00:00');
        $claimService->approveByFinance($claim, $financeUser);

        // 4. Advance Settlement against claim & Audit
        Carbon::setTestNow('2026-11-05 10:00:00');
        $advanceService->settleAdvanceAgainstClaim($advance->fresh(), $claim->fresh(), 1000.0000);

        // 5. Reimbursement & Audit
        Carbon::setTestNow('2026-11-05 11:00:00');
        $reimbursement = $reimbursementService->createReimbursement($employee, [$claim->fresh()], 'bank_payment');
        Carbon::setTestNow('2026-11-05 11:30:00');
        $reimbursementService->approveReimbursement($reimbursement, $financeUser);
        Carbon::setTestNow('2026-11-05 14:00:00');
        $reimbursementService->markAsPaid($reimbursement, ['payment_reference' => 'WIRE-ZR-99'], $financeUser);

        // 6. Accounting GL Export & Period Lock & Audit
        Carbon::setTestNow('2026-11-30 20:00:00');
        $export = $accountingService->generateAccountingExport($tenant->id, '2026-11-01', '2026-11-30', $financeUser);
        Carbon::setTestNow('2026-11-30 20:05:00');
        $accountingService->postAccountingExport($export, $financeUser);
        Carbon::setTestNow('2026-11-30 20:10:00');
        $lock = $accountingService->lockPeriod($tenant->id, '2026-11 Closed', '2026-11-01', '2026-11-30', $financeUser);

        Carbon::setTestNow(); // reset

        // 7. Verify all audit events were logged in audit_events table
        $events = DB::table('audit_events')
            ->where('tenant_id', $tenant->id)
            ->orderBy('occurred_at')
            ->get();

        $this->assertGreaterThanOrEqual(12, $events->count());

        $eventTypes = $events->pluck('event_type')->toArray();
        $this->assertContains('travel_request.created', $eventTypes);
        $this->assertContains('travel_request.submitted', $eventTypes);
        $this->assertContains('travel_request.approved', $eventTypes);
        $this->assertContains('travel_advance.requested', $eventTypes);
        $this->assertContains('travel_advance.approved', $eventTypes);
        $this->assertContains('travel_advance.disbursed', $eventTypes);
        $this->assertContains('expense_claim.created', $eventTypes);
        $this->assertContains('expense_claim.line_added', $eventTypes);
        $this->assertContains('expense_claim.submitted', $eventTypes);
        $this->assertContains('expense_claim.manager_approved', $eventTypes);
        $this->assertContains('expense_claim.finance_approved', $eventTypes);
        $this->assertContains('travel_advance.settled_against_claim', $eventTypes);
        $this->assertContains('expense_reimbursement.created', $eventTypes);
        $this->assertContains('expense_reimbursement.paid', $eventTypes);
        $this->assertContains('expense_accounting.export_generated', $eventTypes);
        $this->assertContains('expense_accounting.period_locked', $eventTypes);

        // 8. Verify tamper-evident cryptographic hash chaining
        $isValid = $auditService->verify($tenant->id);
        $this->assertTrue($isValid, 'Cryptographic hash chain in audit_events should verify successfully.');
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Sophia',
            'last_name' => 'Loren',
            'official_email' => 'sophia.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
