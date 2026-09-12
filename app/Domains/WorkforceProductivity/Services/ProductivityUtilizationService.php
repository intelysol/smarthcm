<?php

namespace App\Domains\WorkforceProductivity\Services;

use App\Domains\WorkforceProductivity\Contracts\ProductivityCalculationInterface;
use App\Domains\WorkforceProductivity\DTOs\UtilizationCalculationData;
use App\Domains\WorkforceProductivity\Models\HcmProductivityTimeRecord;

class ProductivityUtilizationService
{
    public function __construct(
        protected ProductivityCalculationInterface $calculator
    ) {}

    /**
     * Calculate utilization and breakdown across categories for a department or employee.
     */
    public function calculateUtilization(
        string $tenantId,
        ?string $departmentId,
        string $startDate,
        string $endDate,
        float $availableCapacityHours,
        float $scheduledHours = 0.0
    ): UtilizationCalculationData {
        $query = HcmProductivityTimeRecord::where('tenant_id', $tenantId)
            ->whereBetween('record_date', [$startDate, $endDate]);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $records = $query->get();

        $categoryHours = [];
        $productiveHours = 0.0;
        $actualAttendedHours = 0.0;

        foreach ($records as $record) {
            $category = $record->category;
            $hours = (float) $record->hours;

            $categoryHours[$category] = ($categoryHours[$category] ?? 0.0) + $hours;

            if (in_array($category, ['PRODUCTIVE', 'OVERTIME_PRODUCTIVE'], true)) {
                $productiveHours += $hours;
            }

            if ($category !== 'ABSENCE') {
                $actualAttendedHours += $hours;
            }
        }

        $utilizationRate = $this->calculator->calculateUtilization($productiveHours, $availableCapacityHours);
        $scheduleAdherence = $scheduledHours > 0 ? round(($actualAttendedHours / $scheduledHours) * 100.0, 2) : null;
        $scheduleCoverage = $availableCapacityHours > 0 ? round(($scheduledHours / $availableCapacityHours) * 100.0, 2) : null;

        // Determine potential operational bottlenecks from non-productive hours
        $bottleneckFactors = [];
        $waitingHours = $categoryHours['WAITING'] ?? 0.0;
        $idleHours = $categoryHours['IDLE'] ?? 0.0;
        $adminHours = $categoryHours['ADMINISTRATION'] ?? 0.0;

        if ($waitingHours > 0 && ($waitingHours / max(1.0, $actualAttendedHours)) > 0.15) {
            $bottleneckFactors[] = 'Elevated waiting time (>15% of attended hours) - check upstream material/work handoff';
        }
        if ($idleHours > 0 && ($idleHours / max(1.0, $actualAttendedHours)) > 0.10) {
            $bottleneckFactors[] = 'Unallocated idle time detected - check demand shortage or scheduling misalignment';
        }
        if ($adminHours > 0 && ($adminHours / max(1.0, $actualAttendedHours)) > 0.20) {
            $bottleneckFactors[] = 'Excessive administrative overhead (>20% of attended hours)';
        }

        return new UtilizationCalculationData(
            productiveHours: round($productiveHours, 2),
            availableCapacityHours: round($availableCapacityHours, 2),
            scheduledHours: round($scheduledHours, 2),
            actualAttendedHours: round($actualAttendedHours, 2),
            utilizationRate: $utilizationRate,
            scheduleAdherenceRate: $scheduleAdherence,
            scheduleCoverageRate: $scheduleCoverage,
            nonProductiveBreakdown: $categoryHours,
            bottleneckFactors: $bottleneckFactors
        );
    }
}
