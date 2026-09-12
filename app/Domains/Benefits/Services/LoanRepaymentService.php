<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Enums\LoanInstallmentStatus;
use App\Domains\Benefits\Models\LoanApplication;
use App\Domains\Benefits\Models\LoanInstallment;
use App\Domains\Benefits\Models\LoanSchedule;
use App\Domains\Payroll\Models\PayrollRun;
use Illuminate\Support\Facades\DB;

class LoanRepaymentService
{
    public function recordPayrollRepayment(
        LoanInstallment $installment,
        float $paidAmount,
        ?PayrollRun $payrollRun = null,
        ?string $paidDate = null
    ): LoanInstallment {
        return DB::transaction(function () use ($installment, $paidAmount, $payrollRun, $paidDate) {
            $schedule = $installment->schedule;
            $application = $schedule->application;

            $status = $paidAmount >= (float) $installment->total_installment
                ? LoanInstallmentStatus::DEDUCTED_PAYROLL->value
                : LoanInstallmentStatus::MISSED->value;

            $installment->update([
                'paid_amount' => $paidAmount,
                'status' => $status,
                'payroll_run_id' => $payrollRun ? $payrollRun->id : null,
                'paid_date' => $paidDate ?? now()->toDateString(),
            ]);

            $newTotalPaid = (float) $schedule->total_paid + $paidAmount;
            $newRemainingBalance = max(0, (float) $schedule->total_payable - $newTotalPaid);

            $schedule->update([
                'total_paid' => $newTotalPaid,
                'remaining_balance' => $newRemainingBalance,
            ]);

            // Ledger Transaction Entry
            $application->transactions()->create([
                'tenant_id' => $application->tenant_id,
                'employee_id' => $application->employee_id,
                'transaction_type' => 'repayment',
                'transaction_date' => $paidDate ?? now()->toDateString(),
                'amount' => $paidAmount,
                'principal_portion' => (float) $installment->principal_amount,
                'interest_portion' => (float) $installment->interest_amount,
                'running_balance' => $newRemainingBalance,
                'source_reference_type' => $payrollRun ? 'PayrollRun' : 'DirectPayment',
                'source_reference_id' => $payrollRun ? $payrollRun->id : null,
                'notes' => "Repayment Installment #{$installment->installment_number}",
            ]);

            if ($newRemainingBalance <= 0.001) {
                $application->update(['status' => 'closed']);
            }

            return $installment->fresh();
        });
    }
}
