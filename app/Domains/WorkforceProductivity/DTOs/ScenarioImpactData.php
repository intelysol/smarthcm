<?php

namespace App\Domains\WorkforceProductivity\DTOs;

class ScenarioImpactData
{
    public function __construct(
        public readonly string $scenarioName,
        public readonly string $scenarioType,
        public readonly int $headcountDelta,
        public readonly float $fteDelta,
        public readonly float $capacityHoursDelta,
        public readonly float $expectedOutputDelta,
        public readonly float $costDelta,
        public readonly ?float $projectedCostPerUnit,
        public readonly ?float $projectedRoiPct,
        public readonly array $assumptions = [],
    ) {}

    public function toArray(): array
    {
        return [
            'scenario_name' => $this->scenarioName,
            'scenario_type' => $this->scenarioType,
            'headcount_delta' => $this->headcountDelta,
            'fte_delta' => $this->fteDelta,
            'capacity_hours_delta' => $this->capacityHoursDelta,
            'expected_output_delta' => $this->expectedOutputDelta,
            'cost_delta' => $this->costDelta,
            'projected_cost_per_unit' => $this->projectedCostPerUnit,
            'projected_roi_pct' => $this->projectedRoiPct,
            'assumptions' => $this->assumptions,
        ];
    }
}
