<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Expenses\Enums\AccountingExportStatus;
use App\Domains\Expenses\Models\ExpenseAccountingExport;
use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Models\ExpenseClaimLine;
use App\Domains\Expenses\Models\ExpensePeriodLock;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseAccountingService
{
    protected AuditService $auditService;

    public function __construct(?AuditService $auditService = null)
    {
        $this->auditService = $auditService ?? app(AuditService::class);
    }
    public function allocateClaimLine(ExpenseClaimLine $line, array $allocations): void
    {
        $totalPercentage = 0.0;
        foreach ($allocations as $alloc) {
            $totalPercentage += (float) $alloc['allocation_percentage'];
        }

        if (abs($totalPercentage - 100.0) > 0.01) {
            throw ValidationException::withMessages([
                'allocation_percentage' => "Total allocation percentage must equal exactly 100%. Received: {$totalPercentage}%.",
            ]);
        }

        DB::transaction(function () use ($line, $allocations) {
            $line->allocations()->delete();

            $baseAmount = (float) $line->base_amount;
            foreach ($allocations as $alloc) {
                $pct = (float) $alloc['allocation_percentage'];
                $amount = round($baseAmount * ($pct / 100), 4);

                $line->allocations()->create([
                    'tenant_id' => $line->tenant_id,
                    'department_id' => $alloc['department_id'] ?? $line->department_id,
                    'cost_center_id' => $alloc['cost_center_id'] ?? $line->cost_center_id,
                    'project_id' => $alloc['project_id'] ?? $line->project_id,
                    'allocation_percentage' => $pct,
                    'allocated_amount' => $amount,
                    'currency' => $line->base_currency,
                ]);
            }
        });
    }

    public function generateAccountingExport(string $tenantId, string $startDate, string $endDate, User $exportedBy): ExpenseAccountingExport
    {
        return DB::transaction(function () use ($tenantId, $startDate, $endDate, $exportedBy) {
            $start = Carbon::parse($startDate)->toDateString();
            $end = Carbon::parse($endDate)->toDateString();

            $claims = ExpenseClaim::query()
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['approved', 'ready_for_payment', 'paid'])
                ->whereBetween('claim_date', [$start, $end])
                ->with(['lines.category', 'lines.allocations'])
                ->get();

            $glEntries = [];
            $totalDebit = 0.0;
            $totalCredit = 0.0;

            foreach ($claims as $claim) {
                foreach ($claim->lines as $line) {
                    $category = $line->category;
                    $glAccount = $category?->accounting_code ?? 'GL-6100-EXP';
                    $amount = (float) $line->approved_base_amount;
                    $taxAmount = (float) $line->tax_amount;
                    $netExpense = max(0.0, $amount - $taxAmount);

                    // Expense Debit
                    $glEntries[] = [
                        'account_code' => $glAccount,
                        'account_name' => $category?->name ?? 'General Expense',
                        'type' => 'debit',
                        'amount' => $netExpense,
                        'currency' => $line->base_currency,
                        'claim_number' => $claim->claim_number,
                        'cost_center_id' => $line->cost_center_id,
                    ];
                    $totalDebit += $netExpense;

                    // Tax Recoverable Debit
                    if ($taxAmount > 0 && $line->is_tax_recoverable) {
                        $glEntries[] = [
                            'account_code' => 'GL-1300-VAT-REC',
                            'account_name' => 'Tax / VAT Recoverable',
                            'type' => 'debit',
                            'amount' => $taxAmount,
                            'currency' => $line->base_currency,
                            'claim_number' => $claim->claim_number,
                        ];
                        $totalDebit += $taxAmount;
                    }
                }

                // Employee Payable Credit
                $payable = (float) $claim->net_reimbursement_amount;
                if ($payable > 0) {
                    $glEntries[] = [
                        'account_code' => 'GL-2100-EMP-PAY',
                        'account_name' => 'Employee Reimbursement Payable',
                        'type' => 'credit',
                        'amount' => $payable,
                        'currency' => $claim->base_currency,
                        'claim_number' => $claim->claim_number,
                    ];
                    $totalCredit += $payable;
                }

                // Advance Settlement Credit
                $advanceOffset = (float) $claim->advance_settled_amount;
                if ($advanceOffset > 0) {
                    $glEntries[] = [
                        'account_code' => 'GL-1400-TRV-ADV',
                        'account_name' => 'Travel Advances Clearing',
                        'type' => 'credit',
                        'amount' => $advanceOffset,
                        'currency' => $claim->base_currency,
                        'claim_number' => $claim->claim_number,
                    ];
                    $totalCredit += $advanceOffset;
                }
            }

            $batchNumber = 'GL-EXP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $export = ExpenseAccountingExport::create([
                'tenant_id' => $tenantId,
                'batch_number' => $batchNumber,
                'export_date' => now()->toDateString(),
                'total_amount' => $totalDebit,
                'currency' => 'USD',
                'period_start' => $start,
                'period_end' => $end,
                'status' => AccountingExportStatus::EXPORTED->value,
                'gl_export_payload' => [
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
                    'entries_count' => count($glEntries),
                    'entries' => $glEntries,
                ],
                'exported_by' => $exportedBy->id,
                'exported_at' => now(),
            ]);

            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'expense_accounting.export_generated',
                action: 'create',
                entityType: 'ExpenseAccountingExport',
                entityId: $export->id,
                actorId: is_numeric($exportedBy->id) ? (int)$exportedBy->id : null,
                after: ['batch_number' => $batchNumber, 'total_amount' => $totalDebit, 'period_start' => $start, 'period_end' => $end]
            );

            return $export;
        });
    }

    public function postAccountingExport(ExpenseAccountingExport $export, User $user): ExpenseAccountingExport
    {
        $oldStatus = $export->status;
        $export->update([
            'status' => AccountingExportStatus::POSTED->value,
        ]);

        $this->auditService->record(
            tenantId: $export->tenant_id,
            eventType: 'expense_accounting.export_posted',
            action: 'update',
            entityType: 'ExpenseAccountingExport',
            entityId: $export->id,
            actorId: is_numeric($user->id) ? (int)$user->id : null,
            before: ['status' => $oldStatus],
            after: ['status' => AccountingExportStatus::POSTED->value]
        );

        return $export->fresh();
    }

    public function lockPeriod(string $tenantId, string $periodName, string $startDate, string $endDate, User $user): ExpensePeriodLock
    {
        $lock = ExpensePeriodLock::create([
            'tenant_id' => $tenantId,
            'period_name' => $periodName,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_locked' => true,
            'locked_by' => $user->id,
            'locked_at' => now(),
        ]);

        $this->auditService->record(
            tenantId: $tenantId,
            eventType: 'expense_accounting.period_locked',
            action: 'create',
            entityType: 'ExpensePeriodLock',
            entityId: $lock->id,
            actorId: is_numeric($user->id) ? (int)$user->id : null,
            after: ['period_name' => $periodName, 'start_date' => $startDate, 'end_date' => $endDate, 'is_locked' => true]
        );

        return $lock;
    }

    public function unlockPeriod(ExpensePeriodLock $lock, User $user): ExpensePeriodLock
    {
        $lock->update([
            'is_locked' => false,
            'locked_by' => null,
            'locked_at' => null,
        ]);

        $this->auditService->record(
            tenantId: $lock->tenant_id,
            eventType: 'expense_accounting.period_unlocked',
            action: 'update',
            entityType: 'ExpensePeriodLock',
            entityId: $lock->id,
            actorId: is_numeric($user->id) ? (int)$user->id : null,
            after: ['is_locked' => false]
        );

        return $lock->fresh();
    }
}
