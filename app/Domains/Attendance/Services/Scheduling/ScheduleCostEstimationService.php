<?php

namespace App\Domains\Attendance\Services\Scheduling;

use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\RosterPeriod;
use Carbon\CarbonImmutable;

class ScheduleCostEstimationService
{
    /**
     * Estimate projected labor costs and shift premiums for a roster period.
     *
     * @return array{
     *     base_labor_cost: float,
     *     overtime_premium_cost: float,
     *     shift_differential_cost: float,
     *     total_estimated_cost: float
     * }
     */
    public function estimatePeriodCost(RosterPeriod $period, float $defaultHourlyRate = 20.00): array
    {
        $assignments = $period->assignments()->with(['shift', 'employee'])->get();

        $baseLaborCost = 0.00;
        $overtimePremiumCost = 0.00;
        $shiftDifferentialCost = 0.00;

        $employeeHours = [];

        foreach ($assignments as $assignment) {
            $shift = $assignment->shift;
            if (! $shift || $assignment->assignment_status !== 'scheduled') {
                continue;
            }

            $hours = $shift->duration_minutes / 60;
            $shiftCost = $hours * $defaultHourlyRate;
            $baseLaborCost += $shiftCost;

            // Shift differential (night shift +15%)
            if ($shift->is_night_shift) {
                $shiftDifferentialCost += $shiftCost * 0.15;
            }

            // Weekend differential (+10%)
            $carbonDate = CarbonImmutable::parse($assignment->roster_date);
            if ($carbonDate->isWeekend()) {
                $shiftDifferentialCost += $shiftCost * 0.10;
            }

            $empId = $assignment->employee_id;
            $employeeHours[$empId] = ($employeeHours[$empId] ?? 0) + $hours;
        }

        // Overtime projection: hours > 40h per week get 50% overtime premium
        foreach ($employeeHours as $empId => $totalHours) {
            if ($totalHours > 40) {
                $overtimeHours = $totalHours - 40;
                $overtimePremiumCost += $overtimeHours * ($defaultHourlyRate * 0.5);
            }
        }

        $totalEstimatedCost = round($baseLaborCost + $overtimePremiumCost + $shiftDifferentialCost, 2);

        $period->update([
            'estimated_labor_cost' => $totalEstimatedCost,
        ]);

        return [
            'base_labor_cost' => round($baseLaborCost, 2),
            'overtime_premium_cost' => round($overtimePremiumCost, 2),
            'shift_differential_cost' => round($shiftDifferentialCost, 2),
            'total_estimated_cost' => $totalEstimatedCost,
        ];
    }
}
