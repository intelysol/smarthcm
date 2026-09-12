<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Enums\LoanApplicationStatus;
use App\Domains\Benefits\Models\LoanApplication;
use App\Domains\Benefits\Models\LoanProduct;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanApplicationService
{
    public function __construct(
        protected LoanEligibilityService $eligibilityService
    ) {}

    public function apply(Employee $employee, LoanProduct $product, array $data): LoanApplication
    {
        $requestedAmount = (float) $data['requested_amount'];
        $eligibility = $this->eligibilityService->evaluateEligibility($employee, $product, $requestedAmount);

        if (! $eligibility['is_eligible']) {
            throw ValidationException::withMessages([
                'loan_application' => implode(' ', $eligibility['errors']),
            ]);
        }

        $appNumber = 'LOAN-' . strtoupper(uniqid());

        return LoanApplication::create([
            'tenant_id' => $employee->tenant_id,
            'application_number' => $appNumber,
            'employee_id' => $employee->id,
            'loan_product_id' => $product->id,
            'requested_amount' => $requestedAmount,
            'requested_tenure_months' => (int) ($data['requested_tenure_months'] ?? 12),
            'approved_amount' => null,
            'approved_tenure_months' => null,
            'interest_rate' => $product->interest_rate_annual,
            'interest_method' => $product->interest_method,
            'currency' => $product->currency ?? 'USD',
            'purpose' => $data['purpose'] ?? null,
            'status' => LoanApplicationStatus::SUBMITTED->value,
        ]);
    }

    public function approve(LoanApplication $application, array $approvalData, User $approver): LoanApplication
    {
        return DB::transaction(function () use ($application, $approvalData, $approver) {
            $approvedAmount = (float) ($approvalData['approved_amount'] ?? $application->requested_amount);
            $approvedTenure = (int) ($approvalData['approved_tenure_months'] ?? $application->requested_tenure_months);
            $rate = (float) ($approvalData['interest_rate'] ?? $application->interest_rate);

            $application->update([
                'status' => LoanApplicationStatus::APPROVED->value,
                'approved_amount' => $approvedAmount,
                'approved_tenure_months' => $approvedTenure,
                'interest_rate' => $rate,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            // Create Loan Agreement
            $monthlyInstallment = round($approvedAmount / max(1, $approvedTenure), 4);
            $application->agreement()->create([
                'tenant_id' => $application->tenant_id,
                'agreement_number' => 'AGR-' . $application->application_number,
                'principal_amount' => $approvedAmount,
                'interest_rate' => $rate,
                'monthly_installment' => $monthlyInstallment,
                'tenure_months' => $approvedTenure,
                'repayment_start_date' => now()->addMonth()->startOfMonth()->toDateString(),
                'repayment_end_date' => now()->addMonths($approvedTenure)->endOfMonth()->toDateString(),
                'terms_and_conditions' => 'Standard corporate employee loan terms apply.',
                'signed_at' => now(),
            ]);

            return $application->fresh(['agreement']);
        });
    }
}
