<?php

namespace App\Domains\WorkforceProductivity\Services;

use App\Domains\WorkforceProductivity\Contracts\ProductivityCalculationInterface;
use App\Domains\WorkforceProductivity\DTOs\LaborEfficiencyData;

class LaborEfficiencyService
{
    public function __construct(
        protected ProductivityCalculationInterface $calculator
    ) {}

    /**
     * Compute comprehensive labor efficiency metrics integrating operational output and Epic 2.49 workforce cost.
     */
    public function computeEfficiency(
        float $outputVolume,
        float $totalLaborHours,
        float $productiveHours,
        float $paidHours,
        float $overtimeHours,
        float $totalFte,
        float $totalWorkforceCost
    ): LaborEfficiencyData {
        $outputPerLaborHour = $this->calculator->calculateRate($outputVolume, $totalLaborHours);
        $outputPerProductiveHour = $this->calculator->calculateRate($outputVolume, $productiveHours);
        $outputPerFte = $totalFte > 0 ? round($outputVolume / $totalFte, 2) : null;

        $productiveToPaidRatio = $paidHours > 0 ? round(($productiveHours / $paidHours) * 100.0, 2) : null;
        $overtimeRatio = $totalLaborHours > 0 ? round(($overtimeHours / $totalLaborHours) * 100.0, 2) : null;

        $laborCostPerUnit = $this->calculator->calculateUnitCost($totalWorkforceCost, $outputVolume);
        $laborCostPerProductiveHour = $this->calculator->calculateUnitCost($totalWorkforceCost, $productiveHours);
        $outputPerWorkforceDollar = $this->calculator->calculateOutputPerDollar($outputVolume, $totalWorkforceCost);

        return new LaborEfficiencyData(
            outputVolume: round($outputVolume, 4),
            totalLaborHours: round($totalLaborHours, 2),
            productiveHours: round($productiveHours, 2),
            paidHours: round($paidHours, 2),
            overtimeHours: round($overtimeHours, 2),
            totalFte: round($totalFte, 2),
            totalWorkforceCost: round($totalWorkforceCost, 4),
            outputPerLaborHour: $outputPerLaborHour,
            outputPerProductiveHour: $outputPerProductiveHour,
            outputPerFte: $outputPerFte,
            productiveToPaidRatio: $productiveToPaidRatio,
            overtimeRatio: $overtimeRatio,
            laborCostPerUnit: $laborCostPerUnit,
            laborCostPerProductiveHour: $laborCostPerProductiveHour,
            outputPerWorkforceDollar: $outputPerWorkforceDollar
        );
    }
}
