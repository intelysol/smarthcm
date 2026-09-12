<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitContribution;
use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Payroll\Models\PayrollPeriod;

class BenefitContributionService
{
    public function calculatePeriodicContribution(BenefitEnrollment $enrollment, ?PayrollPeriod $period = null): BenefitContribution
    {
        $employeeAmount = (float) $enrollment->employee_contribution;
        $employerAmount = (float) $enrollment->employer_contribution;

        return BenefitContribution::create([
            'tenant_id' => $enrollment->tenant_id,
            'employee_id' => $enrollment->employee_id,
            'benefit_enrollment_id' => $enrollment->id,
            'payroll_period_id' => $period ? $period->id : null,
            'employee_amount' => $employeeAmount,
            'employer_amount' => $employerAmount,
            'currency' => $enrollment->currency ?? 'USD',
            'contribution_date' => $period ? $period->end_date->toDateString() : now()->toDateString(),
            'status' => 'calculated',
        ]);
    }
}
