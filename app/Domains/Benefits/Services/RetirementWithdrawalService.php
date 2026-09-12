<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Enums\RetirementTransactionType;
use App\Domains\Benefits\Models\RetirementAccount;
use App\Domains\Benefits\Models\RetirementWithdrawal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RetirementWithdrawalService
{
    public function __construct(
        protected RetirementVestingService $vestingService
    ) {}

    public function requestWithdrawal(RetirementAccount $account, array $data): RetirementWithdrawal
    {
        $vestedAvailable = $this->vestingService->calculateVestedBalance($account);
        $requested = (float) $data['requested_amount'];

        if ($requested > $vestedAvailable) {
            throw ValidationException::withMessages([
                'requested_amount' => "Requested withdrawal (\${$requested}) exceeds vested available balance (\${$vestedAvailable}).",
            ]);
        }

        return RetirementWithdrawal::create([
            'tenant_id' => $account->tenant_id,
            'retirement_account_id' => $account->id,
            'employee_id' => $account->employee_id,
            'withdrawal_type' => $data['withdrawal_type'] ?? 'partial',
            'requested_amount' => $requested,
            'approved_amount' => 0,
            'tax_withheld' => 0,
            'net_disbursed_amount' => 0,
            'currency' => $account->currency,
            'status' => 'submitted',
            'reason' => $data['reason'] ?? null,
        ]);
    }

    public function approveAndDisburse(RetirementWithdrawal $withdrawal, float $approvedAmount, float $taxWithheld, User $approver): RetirementWithdrawal
    {
        return DB::transaction(function () use ($withdrawal, $approvedAmount, $taxWithheld, $approver) {
            $account = $withdrawal->account;
            $netDisbursed = $approvedAmount - $taxWithheld;

            $withdrawal->update([
                'status' => 'disbursed',
                'approved_amount' => $approvedAmount,
                'tax_withheld' => $taxWithheld,
                'net_disbursed_amount' => $netDisbursed,
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'disbursed_at' => now(),
            ]);

            $newBalance = (float) $account->current_balance - $approvedAmount;

            $account->transactions()->create([
                'tenant_id' => $account->tenant_id,
                'employee_id' => $account->employee_id,
                'transaction_type' => RetirementTransactionType::WITHDRAWAL->value,
                'transaction_date' => now()->toDateString(),
                'amount' => $approvedAmount,
                'running_balance' => $newBalance,
                'currency' => $account->currency,
                'source_reference_type' => 'RetirementWithdrawal',
                'source_reference_id' => $withdrawal->id,
                'description' => "Withdrawal: \${$approvedAmount} (Tax Withheld: \${$taxWithheld}, Net: \${$netDisbursed})",
            ]);

            $account->update([
                'total_withdrawals' => (float) $account->total_withdrawals + $approvedAmount,
                'current_balance' => $newBalance,
            ]);

            return $withdrawal->fresh();
        });
    }
}
