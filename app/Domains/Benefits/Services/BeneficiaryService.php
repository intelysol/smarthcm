<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitBeneficiary;
use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Employee\Models\Employee;
use Illuminate\Validation\ValidationException;

class BeneficiaryService
{
    public function addBeneficiary(Employee $employee, array $data, ?BenefitEnrollment $enrollment = null): BenefitBeneficiary
    {
        $planType = $data['plan_type'] ?? 'life_insurance';
        $newAllocation = (float) ($data['percentage_allocation'] ?? 100.00);

        $currentTotal = (float) BenefitBeneficiary::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('plan_type', $planType)
            ->where('is_primary', true)
            ->sum('percentage_allocation');

        if (($currentTotal + $newAllocation) > 100.001) {
            throw ValidationException::withMessages([
                'percentage_allocation' => "Total primary beneficiary percentage cannot exceed 100%. Current allocated: {$currentTotal}%.",
            ]);
        }

        return BenefitBeneficiary::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'benefit_enrollment_id' => $enrollment ? $enrollment->id : null,
            'plan_type' => $planType,
            'name' => $data['name'],
            'relationship' => $data['relationship'],
            'percentage_allocation' => $newAllocation,
            'contact_phone' => $data['contact_phone'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'effective_from' => $data['effective_from'] ?? now()->toDateString(),
            'is_primary' => $data['is_primary'] ?? true,
            'is_contingent' => $data['is_contingent'] ?? false,
        ]);
    }
}
