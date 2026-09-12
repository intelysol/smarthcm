<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Services;

use App\Domains\Compensation\Models\BonusAllocation;
use App\Domains\Compensation\Models\CompensationCycle;
use App\Domains\Employee\Models\Employee;
use App\Models\User;

class BonusPlanningService
{
    public function __construct(
        protected CompensationCalculationEngine $calculationEngine
    ) {}

    public function calculateAndAllocate(
        User $user,
        CompensationCycle $cycle,
        Employee $employee,
        float $baseSalary,
        float $targetBonusPct,
        float $individualMultiplier = 1.0,
        float $companyMultiplier = 1.0,
        string $bonusType = 'annual_incentive'
    ): BonusAllocation {
        $amount = $this->calculationEngine->calculateIncentiveBonus(
            $baseSalary,
            $targetBonusPct,
            $individualMultiplier,
            $companyMultiplier
        );

        return BonusAllocation::updateOrCreate(
            [
                'compensation_cycle_id' => $cycle->id,
                'employee_id' => $employee->id,
                'bonus_type' => $bonusType,
            ],
            [
                'amount' => $amount,
                'calculation_factors' => [
                    'base_salary' => $baseSalary,
                    'target_bonus_pct' => $targetBonusPct,
                    'individual_multiplier' => $individualMultiplier,
                    'company_multiplier' => $companyMultiplier,
                ],
                'status' => 'allocated',
            ]
        );
    }
}
