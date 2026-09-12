<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\InsuranceCoverage;
use App\Domains\Benefits\Models\InsurancePolicy;
use App\Domains\Employee\Models\Employee;

class InsuranceCoverageService
{
    public function issueCoverage(Employee $employee, InsurancePolicy $policy, array $data, ?BenefitEnrollment $enrollment = null): InsuranceCoverage
    {
        return InsuranceCoverage::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'insurance_policy_id' => $policy->id,
            'benefit_enrollment_id' => $enrollment ? $enrollment->id : null,
            'certificate_number' => $data['certificate_number'] ?? ('CERT-' . strtoupper(uniqid())),
            'coverage_tier' => $data['coverage_tier'] ?? 'employee_only',
            'sum_insured' => (float) ($data['sum_insured'] ?? 50000.00),
            'employee_premium' => (float) ($data['employee_premium'] ?? 0.00),
            'employer_premium' => (float) ($data['employer_premium'] ?? 0.00),
            'effective_from' => $data['effective_from'] ?? now()->toDateString(),
            'effective_to' => $data['effective_to'] ?? null,
            'is_active' => true,
        ]);
    }
}
