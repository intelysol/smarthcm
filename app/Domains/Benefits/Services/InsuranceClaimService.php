<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Enums\ClaimStatus;
use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\InsuranceClaim;
use App\Domains\Benefits\Models\InsurancePolicy;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InsuranceClaimService
{
    public function submitClaim(Employee $employee, array $data, ?InsurancePolicy $policy = null, ?BenefitEnrollment $enrollment = null): InsuranceClaim
    {
        return DB::transaction(function () use ($employee, $data, $policy, $enrollment) {
            $claimNumber = 'CLM-' . strtoupper(uniqid());

            $claim = InsuranceClaim::create([
                'tenant_id' => $employee->tenant_id,
                'insurance_policy_id' => $policy ? $policy->id : null,
                'claim_number' => $claimNumber,
                'employee_id' => $employee->id,
                'benefit_enrollment_id' => $enrollment ? $enrollment->id : null,
                'claim_type' => $data['claim_type'] ?? 'outpatient',
                'incident_date' => $data['incident_date'] ?? now()->toDateString(),
                'service_provider_name' => $data['service_provider_name'] ?? null,
                'claimed_amount' => (float) ($data['claimed_amount'] ?? 0),
                'approved_amount' => 0,
                'currency' => $data['currency'] ?? 'USD',
                'is_sensitive_medical' => $data['is_sensitive_medical'] ?? true,
                'diagnosis_details' => $data['diagnosis_details'] ?? null,
                'status' => ClaimStatus::SUBMITTED->value,
            ]);

            // Add lines if provided
            if (! empty($data['lines']) && is_array($data['lines'])) {
                foreach ($data['lines'] as $line) {
                    $claim->lines()->create([
                        'tenant_id' => $employee->tenant_id,
                        'item_description' => $line['item_description'],
                        'claimed_amount' => (float) $line['claimed_amount'],
                        'approved_amount' => 0,
                        'invoice_number' => $line['invoice_number'] ?? null,
                        'invoice_date' => $line['invoice_date'] ?? now()->toDateString(),
                    ]);
                }
            }

            return $claim->fresh(['lines', 'documents']);
        });
    }

    public function approveClaim(InsuranceClaim $claim, float $approvedAmount, User $approver): InsuranceClaim
    {
        $claim->update([
            'status' => ClaimStatus::APPROVED->value,
            'approved_amount' => $approvedAmount,
            'approved_by' => $approver->id,
            'settled_at' => now(),
        ]);

        return $claim;
    }

    public function rejectClaim(InsuranceClaim $claim, string $reason, User $approver): InsuranceClaim
    {
        $claim->update([
            'status' => ClaimStatus::REJECTED->value,
            'rejection_reason' => $reason,
            'approved_by' => $approver->id,
        ]);

        return $claim;
    }
}
