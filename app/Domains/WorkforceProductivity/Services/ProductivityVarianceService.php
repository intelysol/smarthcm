<?php

namespace App\Domains\WorkforceProductivity\Services;

class ProductivityVarianceService
{
    /**
     * Compute variance between baseline and comparison periods or units.
     * Decomposes variance into Volume, Speed (rate), and Cost components.
     */
    public function calculateVariance(
        array $baseline,
        array $comparison
    ): array {
        $baseOutput = (float) ($baseline['output'] ?? 0.0);
        $compOutput = (float) ($comparison['output'] ?? 0.0);

        $baseHours = (float) ($baseline['hours'] ?? 0.0);
        $compHours = (float) ($comparison['hours'] ?? 0.0);

        $baseCost = (float) ($baseline['cost'] ?? 0.0);
        $compCost = (float) ($comparison['cost'] ?? 0.0);

        $baseRate = $baseHours > 0 ? ($baseOutput / $baseHours) : 0.0;
        $compRate = $compHours > 0 ? ($compOutput / $compHours) : 0.0;

        $baseUnitCost = $baseOutput > 0 ? ($baseCost / $baseOutput) : 0.0;
        $compUnitCost = $compOutput > 0 ? ($compCost / $compOutput) : 0.0;

        $outputDelta = round($compOutput - $baseOutput, 4);
        $outputDeltaPct = $baseOutput > 0 ? round(($outputDelta / $baseOutput) * 100.0, 2) : null;

        $rateDelta = round($compRate - $baseRate, 4);
        $rateDeltaPct = $baseRate > 0 ? round(($rateDelta / $baseRate) * 100.0, 2) : null;

        $costDelta = round($compCost - $baseCost, 4);
        $unitCostDelta = round($compUnitCost - $baseUnitCost, 4);
        $unitCostDeltaPct = $baseUnitCost > 0 ? round(($unitCostDelta / $baseUnitCost) * 100.0, 2) : null;

        // Decompose driver impact:
        // Volume impact on cost = (compOutput - baseOutput) * baseUnitCost
        $volumeCostDriver = round($outputDelta * $baseUnitCost, 4);
        // Rate efficiency impact = compCost - (compOutput * baseUnitCost)
        $efficiencyCostDriver = round($costDelta - $volumeCostDriver, 4);

        return [
            'baseline' => [
                'output' => $baseOutput,
                'hours' => $baseHours,
                'cost' => $baseCost,
                'rate' => round($baseRate, 4),
                'unit_cost' => round($baseUnitCost, 4),
            ],
            'comparison' => [
                'output' => $compOutput,
                'hours' => $compHours,
                'cost' => $compCost,
                'rate' => round($compRate, 4),
                'unit_cost' => round($compUnitCost, 4),
            ],
            'variance' => [
                'output_delta' => $outputDelta,
                'output_delta_pct' => $outputDeltaPct,
                'rate_delta' => $rateDelta,
                'rate_delta_pct' => $rateDeltaPct,
                'cost_delta' => $costDelta,
                'unit_cost_delta' => $unitCostDelta,
                'unit_cost_delta_pct' => $unitCostDeltaPct,
            ],
            'drivers' => [
                'volume_cost_driver' => $volumeCostDriver,
                'efficiency_cost_driver' => $efficiencyCostDriver,
                'primary_driver' => abs($rateDeltaPct ?? 0) > 10 ? 'Productivity Speed Change' : 'Volume Fluctuation',
            ],
        ];
    }
}
