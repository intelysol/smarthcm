<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Enums\LoanApplicationStatus;
use App\Domains\Benefits\Models\LoanApplication;
use App\Domains\Benefits\Models\LoanDisbursement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanDisbursementService
{
    public function __construct(
        protected LoanScheduleService $scheduleService
    ) {}

    public function disburse(LoanApplication $application, array $disbursementData, User $disburser): LoanDisbursement
    {
        if ($application->status !== LoanApplicationStatus::APPROVED->value) {
            throw ValidationException::withMessages([
                'loan_disbursement' => "Loan {$application->application_number} is not approved for disbursement (current status: {$application->status}).",
            ]);
        }

        return DB::transaction(function () use ($application, $disbursementData, $disburser) {
            $disbursedAmount = (float) ($disbursementData['disbursed_amount'] ?? $application->approved_amount);
            $disbursementDate = $disbursementData['disbursement_date'] ?? now()->toDateString();

            $disbursement = LoanDisbursement::create([
                'tenant_id' => $application->tenant_id,
                'loan_application_id' => $application->id,
                'disbursed_amount' => $disbursedAmount,
                'disbursement_date' => $disbursementDate,
                'disbursement_method' => $disbursementData['disbursement_method'] ?? 'bank_transfer',
                'transaction_reference' => $disbursementData['transaction_reference'] ?? ('TXN-' . strtoupper(uniqid())),
                'bank_account_info' => $disbursementData['bank_account_info'] ?? null,
                'disbursed_by' => $disburser->id,
            ]);

            // Generate Amortization Schedule Version 1
            $this->scheduleService->generateSchedule($application, $disbursedAmount, (int) $application->approved_tenure_months, (float) $application->interest_rate, $application->interest_method, $disbursementDate);

            // Record Initial Ledger Transaction
            $application->transactions()->create([
                'tenant_id' => $application->tenant_id,
                'employee_id' => $application->employee_id,
                'transaction_type' => 'disbursement',
                'transaction_date' => $disbursementDate,
                'amount' => $disbursedAmount,
                'principal_portion' => $disbursedAmount,
                'interest_portion' => 0,
                'running_balance' => $disbursedAmount,
                'source_reference_type' => 'LoanDisbursement',
                'source_reference_id' => $disbursement->id,
                'notes' => "Disbursed via {$disbursement->disbursement_method}",
            ]);

            $application->update(['status' => LoanApplicationStatus::DISBURSED->value]);

            return $disbursement;
        });
    }
}
