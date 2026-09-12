<?php

namespace App\Domains\WorkforceProductivity\Services;

use App\Domains\WorkforceProductivity\Contracts\ProductivityCalculationInterface;

class ScheduleEffectivenessService
{
    public function __construct(
        protected ProductivityCalculationInterface $calculator
    ) {}

    /**
     * Evaluate schedule effectiveness by comparing planned schedule vs actual attended vs productive time vs required capacity.
     */
    public function evaluateSchedule(
        float $scheduledHours,
        float $actualAttendedHours,
        float $productiveHours,
        float $requiredCapacityHours = 0.0
    ): array {
        $effectiveness = $this->calculator->calculateScheduleEffectiveness(
            $scheduledHours,
            $actualAttendedHours,
            $productiveHours,
            $requiredCapacityHours
        );

        // Calculate schedule friction metrics
        $adherenceGap = round($actualAttendedHours - $scheduledHours, 2);
        $overtimeDependency = $scheduledHours > 0 && $actualAttendedHours > $scheduledHours
            ? round((($actualAttendedHours - $scheduledHours) / $scheduledHours) * 100.0, 2)
            : 0.0;

        $effectiveness['adherence_gap_hours'] = $adherenceGap;
        $effectiveness['overtime_dependency_pct'] = $overtimeDependency;
        $effectiveness['is_well_covered'] = abs($adherenceGap) <= ($scheduledHours * 0.05);

        return $effectiveness;
    }
}
