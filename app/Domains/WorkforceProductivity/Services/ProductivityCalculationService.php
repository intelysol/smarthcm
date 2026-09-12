<?php

namespace App\Domains\WorkforceProductivity\Services;

use App\Domains\WorkforceProductivity\Contracts\ProductivityCalculationInterface;

class ProductivityCalculationService implements ProductivityCalculationInterface
{
    /**
     * Compute productivity rate safely: Output / Productive Hours.
     * Returns null (N/A) if hours <= 0 or output is null (never divides by zero).
     */
    public function calculateRate(?float $output, ?float $hours): ?float
    {
        if ($output === null || $hours === null || $hours <= 0.0) {
            return null;
        }

        return round($output / $hours, 4);
    }

    /**
     * Compute labor cost per unit safely: Cost / Output.
     * Returns null (N/A) if output <= 0 or cost is null.
     */
    public function calculateUnitCost(?float $cost, ?float $output): ?float
    {
        if ($cost === null || $output === null || $output <= 0.0) {
            return null;
        }

        return round($cost / $output, 4);
    }

    /**
     * Compute utilization rate safely: (Productive Hours / Available Hours) * 100.
     * Returns null if availableHours <= 0.
     */
    public function calculateUtilization(?float $productiveHours, ?float $availableHours): ?float
    {
        if ($productiveHours === null || $availableHours === null || $availableHours <= 0.0) {
            return null;
        }

        return round(($productiveHours / $availableHours) * 100.0, 2);
    }

    /**
     * Compute output generated per workforce dollar: Output / Cost.
     */
    public function calculateOutputPerDollar(?float $output, ?float $cost): ?float
    {
        if ($output === null || $cost === null || $cost <= 0.0) {
            return null;
        }

        return round($output / $cost, 4);
    }

    /**
     * Compare Scheduled Hours vs Actual Attended Hours vs Productive Hours.
     */
    public function calculateScheduleEffectiveness(
        float $scheduledHours,
        float $actualHours,
        float $productiveHours,
        float $requiredCapacityHours = 0.0
    ): array {
        $scheduleAdherence = $scheduledHours > 0 ? round(($actualHours / $scheduledHours) * 100.0, 2) : null;
        $productiveUtilization = $actualHours > 0 ? round(($productiveHours / $actualHours) * 100.0, 2) : null;
        $coverageRatio = $requiredCapacityHours > 0 ? round(($scheduledHours / $requiredCapacityHours) * 100.0, 2) : null;

        return [
            'scheduled_hours' => $scheduledHours,
            'actual_hours' => $actualHours,
            'productive_hours' => $productiveHours,
            'required_capacity_hours' => $requiredCapacityHours,
            'schedule_adherence_pct' => $scheduleAdherence,
            'productive_utilization_pct' => $productiveUtilization,
            'schedule_coverage_pct' => $coverageRatio,
            'unutilized_hours' => max(0, round($actualHours - $productiveHours, 2)),
            'understaffed' => $requiredCapacityHours > $scheduledHours,
            'overstaffed' => $scheduledHours > $requiredCapacityHours && $requiredCapacityHours > 0,
        ];
    }
}
