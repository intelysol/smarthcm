<?php

namespace App\Domains\WorkforceProductivity\DTOs;

class LaborEfficiencyData
{
    public function __construct(
        public readonly float $outputVolume,
        public readonly float $totalLaborHours,
        public readonly float $productiveHours,
        public readonly float $paidHours,
        public readonly float $overtimeHours,
        public readonly float $totalFte,
        public readonly float $totalWorkforceCost,
        public readonly ?float $outputPerLaborHour,
        public readonly ?float $outputPerProductiveHour,
        public readonly ?float $outputPerFte,
        public readonly ?float $productiveToPaidRatio,
        public readonly ?float $overtimeRatio,
        public readonly ?float $laborCostPerUnit,
        public readonly ?float $laborCostPerProductiveHour,
        public readonly ?float $outputPerWorkforceDollar,
    ) {}

    public function toArray(): array
    {
        return [
            'output_volume' => $this->outputVolume,
            'total_labor_hours' => $this->totalLaborHours,
            'productive_hours' => $this->productiveHours,
            'paid_hours' => $this->paidHours,
            'overtime_hours' => $this->overtimeHours,
            'total_fte' => $this->totalFte,
            'total_workforce_cost' => $this->totalWorkforceCost,
            'output_per_labor_hour' => $this->outputPerLaborHour,
            'output_per_productive_hour' => $this->outputPerProductiveHour,
            'output_per_fte' => $this->outputPerFte,
            'productive_to_paid_ratio' => $this->productiveToPaidRatio,
            'overtime_ratio' => $this->overtimeRatio,
            'labor_cost_per_unit' => $this->laborCostPerUnit,
            'labor_cost_per_productive_hour' => $this->laborCostPerProductiveHour,
            'output_per_workforce_dollar' => $this->outputPerWorkforceDollar,
        ];
    }
}
