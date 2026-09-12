<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\RetirementAccount;
use App\Domains\Benefits\Models\RetirementPlan;
use App\Domains\Employee\Models\Employee;
use Carbon\Carbon;

class RetirementVestingService
{
    public function calculateVestedPercentage(Employee $employee, RetirementPlan $plan): float
    {
        if ($plan->vesting_type === 'immediate') {
            return 100.00;
        }

        $serviceYears = $employee->joining_date
            ? Carbon::parse($employee->joining_date)->diffInYears(now())
            : 0;

        $rules = $plan->vestingRules()->reorder('completed_years', 'desc')->get();
        if ($rules->isEmpty()) {
            return 100.00;
        }

        foreach ($rules as $rule) {
            if ($serviceYears >= $rule->completed_years) {
                return (float) $rule->vesting_percentage;
            }
        }

        return 0.00;
    }

    public function calculateVestedBalance(RetirementAccount $account): float
    {
        $employee = $account->employee;
        $plan = $account->plan;

        $vestedPercent = $this->calculateVestedPercentage($employee, $plan);

        $employeePortion = (float) $account->total_employee_contributions;
        $employerPortion = (float) $account->total_employer_contributions;

        // Employee contributions are 100% vested always; Employer contributions vest by rule
        $vestedEmployerPortion = round($employerPortion * ($vestedPercent / 100), 4);

        $vestedTotal = $employeePortion + $vestedEmployerPortion;
        $account->update(['vested_balance' => $vestedTotal]);

        return $vestedTotal;
    }
}
