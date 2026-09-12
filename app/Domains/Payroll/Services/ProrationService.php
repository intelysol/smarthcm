<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Enums\ProrationMethod;
use App\Domains\Payroll\Models\PayrollPeriod;
use Carbon\CarbonImmutable;

class ProrationService
{
    /**
     * Calculate proration factor (0.0 to 1.0) and days worked for an employee in a payroll period.
     *
     * @return array{
     *     factor: float,
     *     eligible_days: int,
     *     total_days: int,
     *     is_prorated: bool,
     *     proration_method: string
     * }
     */
    public function calculateProration(
        Employee $employee,
        PayrollPeriod $period,
        ProrationMethod $method = ProrationMethod::CALENDAR_DAYS
    ): array {
        if ($method === ProrationMethod::NONE) {
            return [
                'factor' => 1.0,
                'eligible_days' => 30,
                'total_days' => 30,
                'is_prorated' => false,
                'proration_method' => $method->value,
            ];
        }

        $periodStart = CarbonImmutable::parse($period->start_date);
        $periodEnd = CarbonImmutable::parse($period->end_date);

        $hireDate = $employee->joining_date ? CarbonImmutable::parse($employee->joining_date) : $periodStart;
        $termDate = $employee->termination_date ? CarbonImmutable::parse($employee->termination_date) : $periodEnd;

        // Effective employment window within the period
        $activeStart = $hireDate->greaterThan($periodStart) ? $hireDate : $periodStart;
        $activeEnd = $termDate->lessThan($periodEnd) ? $termDate : $periodEnd;

        if ($activeStart->greaterThan($periodEnd) || $activeEnd->lessThan($periodStart)) {
            return [
                'factor' => 0.0,
                'eligible_days' => 0,
                'total_days' => (int) $periodStart->diffInDays($periodEnd) + 1,
                'is_prorated' => true,
                'proration_method' => $method->value,
            ];
        }

        switch ($method) {
            case ProrationMethod::WORKING_DAYS:
                $totalWorkingDays = 0;
                $cur = $periodStart;
                while ($cur->lessThanOrEqualTo($periodEnd)) {
                    if (in_array($cur->dayOfWeekIso, [1, 2, 3, 4, 5], true)) {
                        $totalWorkingDays++;
                    }
                    $cur = $cur->addDay();
                }

                $eligibleWorkingDays = 0;
                $cur = $activeStart;
                while ($cur->lessThanOrEqualTo($activeEnd)) {
                    if (in_array($cur->dayOfWeekIso, [1, 2, 3, 4, 5], true)) {
                        $eligibleWorkingDays++;
                    }
                    $cur = $cur->addDay();
                }

                $factor = $totalWorkingDays > 0 ? ($eligibleWorkingDays / $totalWorkingDays) : 1.0;
                return [
                    'factor' => round($factor, 6),
                    'eligible_days' => $eligibleWorkingDays,
                    'total_days' => $totalWorkingDays,
                    'is_prorated' => $eligibleWorkingDays < $totalWorkingDays,
                    'proration_method' => $method->value,
                ];

            case ProrationMethod::FIXED_30_DAYS:
                $calendarDays = (int) $activeStart->diffInDays($activeEnd) + 1;
                $factor = min(1.0, $calendarDays / 30.0);
                return [
                    'factor' => round($factor, 6),
                    'eligible_days' => $calendarDays,
                    'total_days' => 30,
                    'is_prorated' => $calendarDays < 30,
                    'proration_method' => $method->value,
                ];

            case ProrationMethod::CALENDAR_DAYS:
            default:
                $totalDays = (int) $periodStart->diffInDays($periodEnd) + 1;
                $eligibleDays = (int) $activeStart->diffInDays($activeEnd) + 1;
                $factor = $totalDays > 0 ? ($eligibleDays / $totalDays) : 1.0;

                return [
                    'factor' => round($factor, 6),
                    'eligible_days' => $eligibleDays,
                    'total_days' => $totalDays,
                    'is_prorated' => $eligibleDays < $totalDays,
                    'proration_method' => $method->value,
                ];
        }
    }
}
