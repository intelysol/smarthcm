<?php

namespace App\Domains\WorkforceProductivity\DTOs;

class UtilizationCalculationData
{
    public function __construct(
        public readonly float $productiveHours,
        public readonly float $availableCapacityHours,
        public readonly float $scheduledHours,
        public readonly float $actualAttendedHours,
        public readonly ?float $utilizationRate,
        public readonly ?float $scheduleAdherenceRate,
        public readonly ?float $scheduleCoverageRate,
        public readonly array $nonProductiveBreakdown = [],
        public readonly array $bottleneckFactors = [],
    ) {}

    public function toArray(): array
    {
        return [
            'productive_hours' => $this->productiveHours,
            'available_capacity_hours' => $this->availableCapacityHours,
            'scheduled_hours' => $this->scheduledHours,
            'actual_attended_hours' => $this->actualAttendedHours,
            'utilization_rate' => $this->utilizationRate,
            'schedule_adherence_rate' => $this->scheduleAdherenceRate,
            'schedule_coverage_rate' => $this->scheduleCoverageRate,
            'non_productive_breakdown' => $this->nonProductiveBreakdown,
            'bottleneck_factors' => $this->bottleneckFactors,
        ];
    }
}
