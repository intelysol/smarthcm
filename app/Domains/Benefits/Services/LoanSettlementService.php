<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\LoanApplication;
use App\Domains\Benefits\Models\LoanSettlement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoanSettlementService
{
    public function earlySettlement(
        LoanApplication $application,
        float $rebateAmount,
        string $paymentMethod,
        User $settler
    ): LoanSettlement {
        return DB::transaction(function () use ($application, $rebateAmount, $paymentMethod, $settler) {
            $schedule = $application->activeSchedule;

            $unpaidInstallments = $schedule->installments()->where('status', 'scheduled')->get();
            $outstandingPrincipal = (float) $unpaidInstallments->sum('principal_amount');
            $outstandingInterest = (float) $unpaidInstallments->sum('interest_amount');

            $finalAmount = max(0, $outstandingPrincipal + $outstandingInterest - $rebateAmount);

            $settlement = LoanSettlement::create([
                'tenant_id' => $application->tenant_id,
                'loan_application_id' => $application->id,
                'outstanding_principal' => $outstandingPrincipal,
                'outstanding_interest' => $outstandingInterest,
                'rebate_amount' => $rebateAmount,
                'final_settlement_amount' => $finalAmount,
                'settlement_date' => now()->toDateString(),
                'payment_method' => $paymentMethod,
                'settled_by' => $settler->id,
            ]);

            // Mark all scheduled installments as direct_paid or waived
            $schedule->installments()->where('status', 'scheduled')->update(['status' => 'direct_paid']);
            $schedule->update([
                'total_paid' => (float) $schedule->total_payable,
                'remaining_balance' => 0,
            ]);

            // Transaction log
            $application->transactions()->create([
                'tenant_id' => $application->tenant_id,
                'employee_id' => $application->employee_id,
                'transaction_type' => 'settlement',
                'transaction_date' => now()->toDateString(),
                'amount' => $finalAmount,
                'principal_portion' => $outstandingPrincipal,
                'interest_portion' => max(0, $outstandingInterest - $rebateAmount),
                'running_balance' => 0,
                'source_reference_type' => 'LoanSettlement',
                'source_reference_id' => $settlement->id,
                'notes' => "Early Settlement (Rebate: \${$rebateAmount})",
            ]);

            $application->update(['status' => 'closed']);

            return $settlement;
        });
    }
}
