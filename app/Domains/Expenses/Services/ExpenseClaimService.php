<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Enums\ExpenseClaimStatus;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Models\ExpenseClaimLine;
use App\Domains\Expenses\Models\ExpensePeriodLock;
use App\Domains\Expenses\Models\TravelAuthorization;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseClaimService
{
    protected AuditService $auditService;

    public function __construct(
        protected ExpensePolicyService $policyService,
        protected ExpenseValidationService $validationService,
        protected ExchangeRateService $exchangeRateService,
        ?AuditService $auditService = null
    ) {
        $this->auditService = $auditService ?? app(AuditService::class);
    }

    public function createClaim(Employee $employee, array $data, ?TravelAuthorization $travelAuth = null): ExpenseClaim
    {
        return DB::transaction(function () use ($employee, $data, $travelAuth) {
            $claimNumber = 'EXP-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));
            $currency = $data['currency'] ?? ($travelAuth?->currency ?? 'USD');
            $baseCurrency = $data['base_currency'] ?? 'USD';
            $exchangeRate = $this->exchangeRateService->getExchangeRate($employee->tenant_id, $currency, $baseCurrency, $data['claim_date'] ?? null);

            $policy = $this->policyService->resolvePolicyForEmployee($employee, $travelAuth?->travelRequest?->travel_type);

            $claim = ExpenseClaim::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'travel_authorization_id' => $travelAuth?->id ?? ($data['travel_authorization_id'] ?? null),
                'travel_request_id' => $travelAuth?->travel_request_id ?? ($data['travel_request_id'] ?? null),
                'policy_id' => $policy?->id,
                'claim_number' => $claimNumber,
                'title' => $data['title'] ?? 'Business Expense Claim',
                'claim_date' => $data['claim_date'] ?? now()->toDateString(),
                'claimed_total' => 0.0000,
                'approved_total' => 0.0000,
                'advance_settled_amount' => 0.0000,
                'net_reimbursement_amount' => 0.0000,
                'currency' => $currency,
                'base_currency' => $baseCurrency,
                'exchange_rate' => $exchangeRate,
                'status' => ExpenseClaimStatus::DRAFT->value,
                'approval_level' => 1,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->auditService->record(
                tenantId: $employee->tenant_id,
                eventType: 'expense_claim.created',
                action: 'create',
                entityType: 'ExpenseClaim',
                entityId: $claim->id,
                after: ['claim_number' => $claim->claim_number, 'claimed_total' => $claim->claimed_total]
            );

            return $claim;
        });
    }

    public function addLine(ExpenseClaim $claim, array $lineData): ExpenseClaimLine
    {
        $this->assertClaimNotLocked($claim);

        return DB::transaction(function () use ($claim, $lineData) {
            $tenantId = $claim->tenant_id;
            $originalCurrency = $lineData['original_currency'] ?? $claim->currency;
            $baseCurrency = $claim->base_currency;
            $expenseDate = $lineData['expense_date'] ?? now()->toDateString();

            $exchangeRate = $lineData['exchange_rate'] ?? $this->exchangeRateService->getExchangeRate($tenantId, $originalCurrency, $baseCurrency, $expenseDate);
            $originalAmount = (float) $lineData['original_amount'];
            $baseAmount = $this->exchangeRateService->convertAmount($originalAmount, (float) $exchangeRate);

            // Tax calculation
            $taxRate = (float) ($lineData['tax_rate'] ?? 0);
            $taxAmount = (float) ($lineData['tax_amount'] ?? round($baseAmount * ($taxRate / 100), 4));

            $line = ExpenseClaimLine::create([
                'tenant_id' => $tenantId,
                'expense_claim_id' => $claim->id,
                'expense_category_id' => $lineData['expense_category_id'],
                'expense_date' => $expenseDate,
                'merchant' => $lineData['merchant'] ?? null,
                'description' => $lineData['description'],
                'original_amount' => $originalAmount,
                'original_currency' => $originalCurrency,
                'exchange_rate' => $exchangeRate,
                'exchange_rate_date' => $expenseDate,
                'exchange_rate_source' => $lineData['exchange_rate_source'] ?? 'system',
                'base_amount' => $baseAmount,
                'base_currency' => $baseCurrency,
                'tax_type' => $lineData['tax_type'] ?? null,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'is_tax_inclusive' => $lineData['is_tax_inclusive'] ?? false,
                'is_tax_recoverable' => $lineData['is_tax_recoverable'] ?? true,
                'non_recoverable_tax' => $lineData['non_recoverable_tax'] ?? 0.0000,
                'project_id' => $lineData['project_id'] ?? null,
                'project_code' => $lineData['project_code'] ?? null,
                'cost_center_id' => $lineData['cost_center_id'] ?? null,
                'department_id' => $lineData['department_id'] ?? $claim->employee?->department_id,
                'location' => $lineData['location'] ?? null,
                'payment_method' => $lineData['payment_method'] ?? 'personal_card',
                'corporate_card_transaction_id' => $lineData['corporate_card_transaction_id'] ?? null,
                'is_mileage' => $lineData['is_mileage'] ?? false,
                'mileage_distance' => $lineData['mileage_distance'] ?? 0.00,
                'mileage_unit' => $lineData['mileage_unit'] ?? 'km',
                'mileage_rate' => $lineData['mileage_rate'] ?? 0.0000,
                'is_per_diem' => $lineData['is_per_diem'] ?? false,
                'per_diem_days' => $lineData['per_diem_days'] ?? 0.00,
                'per_diem_rate' => $lineData['per_diem_rate'] ?? 0.0000,
                'meal_deductions' => $lineData['meal_deductions'] ?? 0.0000,
                'policy_status' => 'compliant',
                'approved_amount' => $baseAmount,
                'approved_base_amount' => $baseAmount,
            ]);

            // Validate against active policy
            $policy = $claim->policy;
            $version = $policy ? $this->policyService->getActiveVersionForDate($policy, $expenseDate) : null;
            $this->validationService->validateClaimLine($line, $version);

            $claim->recalculateTotals();

            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'expense_claim.line_added',
                action: 'create',
                entityType: 'ExpenseClaimLine',
                entityId: $line->id,
                after: ['claim_id' => $claim->id, 'base_amount' => $baseAmount, 'policy_status' => $line->policy_status]
            );

            return $line->fresh(['category', 'policyResults']);
        });
    }

    public function submitClaim(ExpenseClaim $claim): ExpenseClaim
    {
        $this->assertClaimNotLocked($claim);

        if ($claim->lines()->count() === 0) {
            throw ValidationException::withMessages([
                'lines' => 'Cannot submit an expense claim without any expense lines.',
            ]);
        }

        $oldStatus = $claim->status;
        $claim->update([
            'status' => ExpenseClaimStatus::SUBMITTED->value,
            'approval_level' => 1,
        ]);

        $this->auditService->record(
            tenantId: $claim->tenant_id,
            eventType: 'expense_claim.submitted',
            action: 'update',
            entityType: 'ExpenseClaim',
            entityId: $claim->id,
            before: ['status' => $oldStatus],
            after: ['status' => ExpenseClaimStatus::SUBMITTED->value, 'claimed_total' => $claim->claimed_total]
        );

        return $claim->fresh();
    }

    public function approveByManager(ExpenseClaim $claim, User $manager): ExpenseClaim
    {
        $this->assertClaimNotLocked($claim);

        return DB::transaction(function () use ($claim, $manager) {
            $oldStatus = $claim->status;
            $claim->update([
                'status' => ExpenseClaimStatus::FINANCE_REVIEW->value,
                'approved_by' => $manager->id,
                'approved_at' => now(),
                'approval_level' => 2,
            ]);

            $this->auditService->record(
                tenantId: $claim->tenant_id,
                eventType: 'expense_claim.manager_approved',
                action: 'update',
                entityType: 'ExpenseClaim',
                entityId: $claim->id,
                actorId: is_numeric($manager->id) ? (int)$manager->id : null,
                before: ['status' => $oldStatus],
                after: ['status' => ExpenseClaimStatus::FINANCE_REVIEW->value, 'manager_id' => $manager->id]
            );

            return $claim->fresh();
        });
    }

    public function approveByFinance(ExpenseClaim $claim, User $financeUser): ExpenseClaim
    {
        $this->assertClaimNotLocked($claim);

        return DB::transaction(function () use ($claim, $financeUser) {
            $oldStatus = $claim->status;
            $claim->update([
                'status' => ExpenseClaimStatus::APPROVED->value,
                'finance_reviewed_by' => $financeUser->id,
                'finance_reviewed_at' => now(),
            ]);

            $this->auditService->record(
                tenantId: $claim->tenant_id,
                eventType: 'expense_claim.finance_approved',
                action: 'update',
                entityType: 'ExpenseClaim',
                entityId: $claim->id,
                actorId: is_numeric($financeUser->id) ? (int)$financeUser->id : null,
                before: ['status' => $oldStatus],
                after: ['status' => ExpenseClaimStatus::APPROVED->value, 'finance_reviewed_by' => $financeUser->id]
            );

            return $claim->fresh();
        });
    }

    public function overridePolicyViolation(ExpenseClaimLine $line, User $overrideApprover, string $reason): ExpenseClaimLine
    {
        $oldStatus = $line->policy_status;
        $line->update([
            'policy_status' => 'compliant',
            'approved_amount' => $line->base_amount,
            'approved_base_amount' => $line->base_amount,
            'override_approver_id' => $overrideApprover->id,
            'override_at' => now(),
            'exception_reason' => $reason,
        ]);

        // Resolve open exceptions for this line
        $line->claim->exceptions()
            ->where('expense_claim_line_id', $line->id)
            ->update([
                'status' => 'approved',
                'resolved_by' => $overrideApprover->id,
                'resolved_at' => now(),
                'resolution_notes' => "Policy override approved: {$reason}",
            ]);

        $line->claim->recalculateTotals();

        $this->auditService->record(
            tenantId: $line->tenant_id,
            eventType: 'expense_claim.policy_override',
            action: 'update',
            entityType: 'ExpenseClaimLine',
            entityId: $line->id,
            actorId: is_numeric($overrideApprover->id) ? (int)$overrideApprover->id : null,
            before: ['policy_status' => $oldStatus],
            after: ['policy_status' => 'compliant', 'reason' => $reason]
        );

        return $line->fresh();
    }

    public function assertClaimNotLocked(ExpenseClaim $claim): void
    {
        if ($claim->is_period_locked) {
            throw ValidationException::withMessages([
                'claim' => 'This expense claim is in a locked accounting period and cannot be modified.',
            ]);
        }
    }
}
