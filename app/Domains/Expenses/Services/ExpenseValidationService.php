<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Expenses\Enums\PolicyViolationAction;
use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Models\ExpenseClaimLine;
use App\Domains\Expenses\Models\ExpenseException;
use App\Domains\Expenses\Models\ExpensePolicyResult;
use App\Domains\Expenses\Models\ExpensePolicyVersion;

class ExpenseValidationService
{
    public function validateClaimLine(ExpenseClaimLine $line, ?ExpensePolicyVersion $policyVersion = null): array
    {
        $tenantId = $line->tenant_id;
        $category = $line->category;
        $amount = (float) $line->base_amount;
        $violations = [];
        $policyStatus = 'compliant';
        $approvedAmount = $amount;

        // 1. Category Max Amount Check
        if ($category && $category->max_amount !== null && $amount > (float) $category->max_amount) {
            $excess = $amount - (float) $category->max_amount;
            $violations[] = [
                'rule_name' => "Category Cap: {$category->name}",
                'rule_type' => 'category_limit',
                'allowed_limit' => (float) $category->max_amount,
                'actual_amount' => $amount,
                'excess_amount' => $excess,
                'action_taken' => PolicyViolationAction::WARNING->value,
                'violation_details' => "Amount \${$amount} exceeds category limit of \${$category->max_amount}",
            ];
            $policyStatus = 'warning';
        }

        // 2. Policy Version Limits (Daily Meal / Hotel)
        if ($policyVersion) {
            $catType = $category?->category_type ?? 'general';

            if ($catType === 'meals' && $policyVersion->daily_meal_limit !== null && $amount > (float) $policyVersion->daily_meal_limit) {
                $excess = $amount - (float) $policyVersion->daily_meal_limit;
                $violations[] = [
                    'rule_name' => 'Daily Meal Policy Limit',
                    'rule_type' => 'daily_meal_limit',
                    'allowed_limit' => (float) $policyVersion->daily_meal_limit,
                    'actual_amount' => $amount,
                    'excess_amount' => $excess,
                    'action_taken' => PolicyViolationAction::EXCESS_REDUCED->value,
                    'violation_details' => "Meal expense \${$amount} exceeds daily cap of \${$policyVersion->daily_meal_limit}. Capped at allowable limit.",
                ];
                $policyStatus = 'excess_reduced';
                $approvedAmount = (float) $policyVersion->daily_meal_limit;
            }

            if ($catType === 'accommodation' && $policyVersion->daily_hotel_limit !== null && $amount > (float) $policyVersion->daily_hotel_limit) {
                $excess = $amount - (float) $policyVersion->daily_hotel_limit;
                $violations[] = [
                    'rule_name' => 'Daily Hotel Policy Limit',
                    'rule_type' => 'daily_hotel_limit',
                    'allowed_limit' => (float) $policyVersion->daily_hotel_limit,
                    'actual_amount' => $amount,
                    'excess_amount' => $excess,
                    'action_taken' => PolicyViolationAction::EXCESS_REDUCED->value,
                    'violation_details' => "Hotel expense \${$amount} exceeds daily cap of \${$policyVersion->daily_hotel_limit}.",
                ];
                $policyStatus = 'excess_reduced';
                $approvedAmount = (float) $policyVersion->daily_hotel_limit;
            }

            // Receipt Requirement Check
            $receiptThreshold = (float) ($policyVersion->receipt_required_threshold ?: ($category?->receipt_threshold ?? 0));
            $hasReceipt = $line->receipts()->exists();

            if ($amount > $receiptThreshold && ! $hasReceipt && ($category?->receipt_required ?? true)) {
                $violations[] = [
                    'rule_name' => 'Missing Mandatory Receipt',
                    'rule_type' => 'receipt_required',
                    'allowed_limit' => $receiptThreshold,
                    'actual_amount' => $amount,
                    'excess_amount' => 0.0000,
                    'action_taken' => PolicyViolationAction::EXCEPTION_REQUIRED->value,
                    'violation_details' => "Receipt required for expenses exceeding \${$receiptThreshold}.",
                ];
                $policyStatus = 'exception_required';

                // Create Exception
                ExpenseException::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'expense_claim_id' => $line->expense_claim_id,
                        'expense_claim_line_id' => $line->id,
                        'exception_type' => 'missing_receipt',
                    ],
                    [
                        'severity' => 'blocker',
                        'reason' => "Expense of \${$amount} exceeds threshold of \${$receiptThreshold} without attached receipt.",
                        'status' => 'open',
                    ]
                );
            }
        }

        // 3. Duplicate Detection Check
        if ($this->detectDuplicateExpenseLine($line)) {
            $violations[] = [
                'rule_name' => 'Potential Duplicate Expense Line',
                'rule_type' => 'duplicate_detection',
                'allowed_limit' => null,
                'actual_amount' => $amount,
                'excess_amount' => 0.0000,
                'action_taken' => PolicyViolationAction::WARNING->value,
                'violation_details' => "Identical date, merchant, and amount already exists on another claim line for this employee.",
            ];

            ExpenseException::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'expense_claim_id' => $line->expense_claim_id,
                    'expense_claim_line_id' => $line->id,
                    'exception_type' => 'duplicate_expense',
                ],
                [
                    'severity' => 'warning',
                    'reason' => "Potential duplicate claim on {$line->expense_date->format('Y-m-d')} for merchant '{$line->merchant}' amount \${$amount}.",
                    'status' => 'open',
                ]
            );
        }

        // Persist policy results
        $line->policyResults()->delete();
        foreach ($violations as $v) {
            $line->policyResults()->create(array_merge($v, [
                'tenant_id' => $tenantId,
                'expense_policy_id' => $policyVersion?->expense_policy_id,
                'is_violation' => true,
            ]));
        }

        $line->update([
            'policy_status' => $policyStatus,
            'approved_amount' => $approvedAmount,
            'approved_base_amount' => $approvedAmount,
        ]);

        return [
            'policy_status' => $policyStatus,
            'approved_amount' => $approvedAmount,
            'violations_count' => count($violations),
            'violations' => $violations,
        ];
    }

    public function detectDuplicateExpenseLine(ExpenseClaimLine $line): bool
    {
        $claim = $line->claim;
        if (! $claim) {
            return false;
        }

        return ExpenseClaimLine::query()
            ->where('tenant_id', $line->tenant_id)
            ->where('id', '!=', $line->id)
            ->whereHas('claim', fn ($q) => $q->where('employee_id', $claim->employee_id))
            ->whereDate('expense_date', $line->expense_date->toDateString())
            ->where('base_amount', $line->base_amount)
            ->where(function ($q) use ($line) {
                if ($line->merchant) {
                    $q->where('merchant', $line->merchant);
                }
            })
            ->exists();
    }
}
