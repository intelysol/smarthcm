<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Services;

use App\Domains\Compensation\Models\TotalRewardsStatement;
use App\Domains\Employee\Models\Employee;
use App\Models\User;

class TotalRewardsService
{
    /**
     * Generate or update a Total Rewards Statement for an employee
     */
    public function generateStatement(
        User $user,
        Employee $employee,
        int $year,
        float $baseSalary,
        float $bonusAmount = 0.0,
        float $benefitsValue = 0.0,
        float $equityValue = 0.0,
        float $retirementContribution = 0.0,
        float $otherAllowances = 0.0
    ): TotalRewardsStatement {
        $totalInvestment = $baseSalary + $bonusAmount + $benefitsValue + $equityValue + $retirementContribution + $otherAllowances;

        $components = [
            'base_pay' => [
                'amount' => $baseSalary,
                'percentage' => $totalInvestment > 0 ? round(($baseSalary / $totalInvestment) * 100, 2) : 0,
            ],
            'variable_pay' => [
                'amount' => $bonusAmount,
                'percentage' => $totalInvestment > 0 ? round(($bonusAmount / $totalInvestment) * 100, 2) : 0,
            ],
            'health_and_wellness' => [
                'amount' => $benefitsValue,
                'percentage' => $totalInvestment > 0 ? round(($benefitsValue / $totalInvestment) * 100, 2) : 0,
            ],
            'retirement_savings' => [
                'amount' => $retirementContribution,
                'percentage' => $totalInvestment > 0 ? round(($retirementContribution / $totalInvestment) * 100, 2) : 0,
            ],
            'equity_and_ownership' => [
                'amount' => $equityValue,
                'percentage' => $totalInvestment > 0 ? round(($equityValue / $totalInvestment) * 100, 2) : 0,
            ],
            'allowances_and_perks' => [
                'amount' => $otherAllowances,
                'percentage' => $totalInvestment > 0 ? round(($otherAllowances / $totalInvestment) * 100, 2) : 0,
            ],
        ];

        return TotalRewardsStatement::updateOrCreate(
            [
                'tenant_id' => $user->tenant_id,
                'employee_id' => $employee->id,
                'year' => $year,
            ],
            [
                'components' => $components,
                'total_employer_investment' => $totalInvestment,
            ]
        );
    }
}
