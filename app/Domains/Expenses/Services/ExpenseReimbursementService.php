<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Enums\ExpenseClaimStatus;
use App\Domains\Expenses\Enums\ReimbursementMethod;
use App\Domains\Expenses\Enums\ReimbursementStatus;
use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Models\ExpenseReimbursement;
use App\Domains\Expenses\Models\ExpenseReimbursementLine;
use App\Domains\Payroll\Models\PayrollInputLine;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseReimbursementService
{
    protected AuditService $auditService;

    public function __construct(?AuditService $auditService = null)
    {
        $this->auditService = $auditService ?? app(AuditService::class);
    }

    public function createReimbursement(Employee $employee, array $claims, string $method = 'bank_payment', ?PayrollPeriod $payrollPeriod = null): ExpenseReimbursement
    {
        return DB::transaction(function () use ($employee, $claims, $method, $payrollPeriod) {
            $reimbursementNumber = 'REIMB-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));
            $totalPayable = 0.0;
            $lineRecords = [];

            foreach ($claims as $claim) {
                if (! $claim instanceof ExpenseClaim) {
                    $claim = ExpenseClaim::findOrFail($claim);
                }

                if ($claim->status !== ExpenseClaimStatus::APPROVED->value && $claim->status !== ExpenseClaimStatus::FINANCE_REVIEW->value) {
                    throw ValidationException::withMessages([
                        'claim' => "Claim {$claim->claim_number} is not approved for reimbursement.",
                    ]);
                }

                $claimed = (float) $claim->claimed_total;
                $approved = (float) $claim->approved_total;
                $advanceOffset = (float) $claim->advance_settled_amount;
                $payable = max(0.0, $approved - $advanceOffset);

                $totalPayable += $payable;

                $lineRecords[] = [
                    'claim' => $claim,
                    'claimed_amount' => $claimed,
                    'approved_amount' => $approved,
                    'advance_deduction_amount' => $advanceOffset,
                    'payable_amount' => $payable,
                    'currency' => $claim->currency,
                ];
            }

            $reimbursement = ExpenseReimbursement::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'reimbursement_number' => $reimbursementNumber,
                'total_reimbursement_amount' => $totalPayable,
                'currency' => $claims[0]->currency ?? 'USD',
                'reimbursement_method' => $method,
                'payroll_period_id' => $payrollPeriod?->id,
                'status' => ReimbursementStatus::PENDING->value,
            ]);

            foreach ($lineRecords as $rec) {
                $reimbursement->lines()->create([
                    'tenant_id' => $reimbursement->tenant_id,
                    'expense_claim_id' => $rec['claim']->id,
                    'claimed_amount' => $rec['claimed_amount'],
                    'approved_amount' => $rec['approved_amount'],
                    'advance_deduction_amount' => $rec['advance_deduction_amount'],
                    'payable_amount' => $rec['payable_amount'],
                    'currency' => $rec['currency'],
                ]);

                $rec['claim']->update([
                    'status' => ExpenseClaimStatus::READY_FOR_PAYMENT->value,
                ]);
            }

            $this->auditService->record(
                tenantId: $employee->tenant_id,
                eventType: 'expense_reimbursement.created',
                action: 'create',
                entityType: 'ExpenseReimbursement',
                entityId: $reimbursement->id,
                after: ['reimbursement_number' => $reimbursementNumber, 'total_amount' => $totalPayable, 'method' => $method]
            );

            return $reimbursement->fresh(['lines', 'employee']);
        });
    }

    public function approveReimbursement(ExpenseReimbursement $reimbursement, User $approver): ExpenseReimbursement
    {
        $oldStatus = $reimbursement->status;
        $reimbursement->update([
            'status' => ReimbursementStatus::APPROVED->value,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        $this->auditService->record(
            tenantId: $reimbursement->tenant_id,
            eventType: 'expense_reimbursement.approved',
            action: 'update',
            entityType: 'ExpenseReimbursement',
            entityId: $reimbursement->id,
            actorId: is_numeric($approver->id) ? (int)$approver->id : null,
            before: ['status' => $oldStatus],
            after: ['status' => ReimbursementStatus::APPROVED->value]
        );

        return $reimbursement->fresh();
    }

    public function markAsPaid(ExpenseReimbursement $reimbursement, array $paymentData, User $paidBy): ExpenseReimbursement
    {
        return DB::transaction(function () use ($reimbursement, $paymentData, $paidBy) {
            $paymentRef = $paymentData['payment_reference'] ?? ('PAY-' . strtoupper(uniqid()));
            $paymentDate = $paymentData['payment_date'] ?? now()->toDateString();
            $oldStatus = $reimbursement->status;

            $reimbursement->update([
                'status' => ReimbursementStatus::PAID->value,
                'payment_reference' => $paymentRef,
                'payment_date' => $paymentDate,
                'paid_by' => $paidBy->id,
                'paid_at' => now(),
            ]);

            // Update attached claims to paid & lock period
            foreach ($reimbursement->lines as $line) {
                $line->claim?->update([
                    'status' => ExpenseClaimStatus::PAID->value,
                    'is_period_locked' => true,
                ]);
            }

            $this->auditService->record(
                tenantId: $reimbursement->tenant_id,
                eventType: 'expense_reimbursement.paid',
                action: 'update',
                entityType: 'ExpenseReimbursement',
                entityId: $reimbursement->id,
                actorId: is_numeric($paidBy->id) ? (int)$paidBy->id : null,
                before: ['status' => $oldStatus],
                after: ['status' => ReimbursementStatus::PAID->value, 'payment_reference' => $paymentRef]
            );

            return $reimbursement->fresh();
        });
    }

    public function syncToPayrollPeriod(ExpenseReimbursement $reimbursement, PayrollPeriod $period): ?PayrollInputLine
    {
        if ($reimbursement->reimbursement_method !== ReimbursementMethod::PAYROLL->value) {
            return null;
        }

        $input = \App\Domains\Payroll\Models\PayrollInput::firstOrCreate(
            [
                'tenant_id' => $reimbursement->tenant_id,
                'payroll_period_id' => $period->id,
                'employee_id' => $reimbursement->employee_id,
            ],
            [
                'status' => 'draft',
                'collected_at' => now(),
            ]
        );

        $line = $input->lines()->create([
            'tenant_id' => $reimbursement->tenant_id,
            'payroll_input_id' => $input->id,
            'employee_id' => $reimbursement->employee_id,
            'source_module' => 'expenses',
            'source_entity_type' => 'expense_reimbursement',
            'source_entity_id' => $reimbursement->id,
            'input_type' => 'expense_reimbursement',
            'amount' => $reimbursement->total_reimbursement_amount,
            'currency' => $reimbursement->currency,
            'effective_date' => $period->cutoff_date ?? now()->toDateString(),
            'approval_status' => 'approved',
            'notes' => "Expense Reimbursement #{$reimbursement->reimbursement_number}",
        ]);

        $reimbursement->update([
            'payroll_period_id' => $period->id,
            'status' => ReimbursementStatus::PROCESSING->value,
        ]);

        return $line;
    }
}
