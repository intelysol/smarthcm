<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\RetirementAccount;
use App\Domains\Benefits\Models\RetirementPlan;
use App\Domains\Employee\Models\Employee;

class RetirementAccountService
{
    public function getAccountStatement(RetirementAccount $account): array
    {
        $transactions = $account->transactions()->orderBy('transaction_date')->get();

        $calculatedBalance = 0;
        foreach ($transactions as $tx) {
            if ($tx->transaction_type === 'withdrawal' || $tx->transaction_type === 'transfer') {
                $calculatedBalance -= (float) $tx->amount;
            } else {
                $calculatedBalance += (float) $tx->amount;
            }
        }

        return [
            'account_number' => $account->account_number,
            'currency' => $account->currency,
            'opening_balance' => (float) $account->opening_balance,
            'employee_contributions' => (float) $account->total_employee_contributions,
            'employer_contributions' => (float) $account->total_employer_contributions,
            'withdrawals' => (float) $account->total_withdrawals,
            'current_balance' => (float) $account->current_balance,
            'ledger_verified_balance' => round($calculatedBalance, 4),
            'is_ledger_consistent' => abs($calculatedBalance - (float) $account->current_balance) < 0.001,
            'transactions_count' => $transactions->count(),
        ];
    }
}
