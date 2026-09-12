<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitContribution;
use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\InsuranceClaim;
use App\Domains\Benefits\Models\LoanApplication;
use App\Domains\Benefits\Models\RetirementAccount;
use App\Domains\Employee\Models\Employee;

class BenefitsAnalyticsService
{
    public function getExecutiveSummary(string $tenantId): array
    {
        $activeEnrollments = BenefitEnrollment::where('tenant_id', $tenantId)
            ->whereIn('status', ['approved', 'active'])
            ->count();

        $totalClaimed = (float) InsuranceClaim::where('tenant_id', $tenantId)->sum('claimed_amount');
        $totalPaidClaims = (float) InsuranceClaim::where('tenant_id', $tenantId)->sum('approved_amount');

        $activeLoansCount = LoanApplication::where('tenant_id', $tenantId)
            ->where('status', 'disbursed')
            ->count();

        $totalRetirementAssets = (float) RetirementAccount::where('tenant_id', $tenantId)->sum('current_balance');

        return [
            'active_enrollments' => $activeEnrollments,
            'total_claims_amount' => $totalClaimed,
            'total_approved_claims' => $totalPaidClaims,
            'active_disbursed_loans' => $activeLoansCount,
            'total_retirement_fund_assets' => $totalRetirementAssets,
        ];
    }

    public function calculateTotalEmploymentCost(Employee $employee, float $basicSalary): array
    {
        $tenantId = $employee->tenant_id;

        $employerInsurance = (float) BenefitEnrollment::where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['approved', 'active'])
            ->sum('employer_contribution');

        $employerRetirement = 0.0;
        $retAcc = RetirementAccount::where('tenant_id', $tenantId)->where('employee_id', $employee->id)->first();
        if ($retAcc && $retAcc->plan) {
            $emprRate = (float) $retAcc->plan->max_employer_contribution_rate;
            $employerRetirement = round($basicSalary * ($emprRate / 100), 4);
        }

        $totalEmploymentCost = $basicSalary + $employerInsurance + $employerRetirement;

        return [
            'employee_id' => $employee->id,
            'employee_name' => "{$employee->first_name} {$employee->last_name}",
            'basic_salary' => $basicSalary,
            'employer_insurance_cost' => $employerInsurance,
            'employer_retirement_cost' => $employerRetirement,
            'total_employment_cost' => round($totalEmploymentCost, 4),
        ];
    }
}
