<?php

namespace App\Domains\WorkforceProductivity\Services;

use App\Domains\WorkforceProductivity\Contracts\ProductivityCalculationInterface;
use App\Domains\WorkforceProductivity\DTOs\ScenarioImpactData;
use App\Domains\WorkforceProductivity\Models\HcmProductivityScenario;
use App\Domains\WorkforceProductivity\Models\HcmProductivitySnapshot;
use Illuminate\Support\Str;

class ProductivityScenarioService
{
    public function __construct(
        protected ProductivityCalculationInterface $calculator
    ) {}

    /**
     * Simulate workforce scenario and evaluate operational and financial impact.
     */
    public function simulateScenario(
        string $tenantId,
        string $scenarioName,
        string $scenarioType,
        ?string $baselineSnapshotId,
        int $headcountDelta,
        float $avgCostPerHead,
        float $avgMonthlyHoursPerHead,
        float $baselineHourlyOutputRate,
        float $valuePerUnit,
        array $assumptions = []
    ): HcmProductivityScenario {
        $fteDelta = round($headcountDelta * 1.0, 2);
        $capacityHoursDelta = round($fteDelta * $avgMonthlyHoursPerHead, 2);
        $expectedOutputDelta = round($capacityHoursDelta * $baselineHourlyOutputRate, 4);
        $costDelta = round($headcountDelta * $avgCostPerHead, 4);

        // Fetch baseline values if snapshot available
        $baselineOutput = 0.0;
        $baselineCost = 0.0;
        if ($baselineSnapshotId) {
            $snapshot = HcmProductivitySnapshot::find($baselineSnapshotId);
            if ($snapshot) {
                $baselineOutput = (float) $snapshot->total_output;
                $baselineCost = (float) $snapshot->total_labor_cost;
            }
        }

        $projectedTotalOutput = max(0.0, $baselineOutput + $expectedOutputDelta);
        $projectedTotalCost = max(0.0, $baselineCost + $costDelta);

        $projectedCostPerUnit = $this->calculator->calculateUnitCost($projectedTotalCost, $projectedTotalOutput);

        // Operational benefit = expectedOutputDelta * valuePerUnit
        $grossBenefit = round($expectedOutputDelta * $valuePerUnit, 4);
        $netBenefit = $grossBenefit - $costDelta;
        $projectedRoi = $costDelta > 0 ? round(($netBenefit / $costDelta) * 100.0, 2) : null;

        return HcmProductivityScenario::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'scenario_name' => $scenarioName,
            'scenario_type' => $scenarioType,
            'baseline_snapshot_id' => $baselineSnapshotId,
            'headcount_delta' => $headcountDelta,
            'fte_delta' => $fteDelta,
            'capacity_hours_delta' => $capacityHoursDelta,
            'expected_output_delta' => $expectedOutputDelta,
            'cost_delta' => $costDelta,
            'projected_cost_per_unit' => $projectedCostPerUnit,
            'projected_roi_pct' => $projectedRoi,
            'assumptions' => array_merge($assumptions, [
                'avg_cost_per_head' => $avgCostPerHead,
                'avg_monthly_hours_per_head' => $avgMonthlyHoursPerHead,
                'baseline_rate' => $baselineHourlyOutputRate,
                'unit_value' => $valuePerUnit,
                'gross_benefit' => $grossBenefit,
                'net_benefit' => $netBenefit,
            ]),
        ]);
    }
}
