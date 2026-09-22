<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class ProrationCalculator
{
    /**
     * Calculate deterministic prorated amount for mid-cycle plan changes.
     *
     * @param float $currentRecurringAmount
     * @param float $newRecurringAmount
     * @param CarbonInterface $cycleStart
     * @param CarbonInterface $cycleEnd
     * @param CarbonInterface|null $changeDate
     * @return array{
     *     total_seconds: int,
     *     remaining_seconds: int,
     *     remaining_ratio: float,
     *     unused_current_credit: float,
     *     new_plan_charge: float,
     *     net_adjustment: float,
     *     immediate_charge: float,
     *     credit_issued: float
     * }
     */
    public function calculate(
        float $currentRecurringAmount,
        float $newRecurringAmount,
        CarbonInterface $cycleStart,
        CarbonInterface $cycleEnd,
        ?CarbonInterface $changeDate = null
    ): array {
        $changeDate = $changeDate ?? Carbon::now();

        $startTs = $cycleStart->getTimestamp();
        $endTs = $cycleEnd->getTimestamp();
        $changeTs = $changeDate->getTimestamp();

        // Bound change timestamp within the cycle
        $effectiveChangeTs = max($startTs, min($endTs, $changeTs));

        $totalSeconds = max(1, $endTs - $startTs);
        $remainingSeconds = max(0, $endTs - $effectiveChangeTs);
        $ratio = $remainingSeconds / $totalSeconds;

        $unusedCurrentCredit = round($currentRecurringAmount * $ratio, 2);
        $newPlanCharge = round($newRecurringAmount * $ratio, 2);
        $netAdjustment = round($newPlanCharge - $unusedCurrentCredit, 2);

        $immediateCharge = max(0.0, $netAdjustment);
        $creditIssued = $netAdjustment < 0 ? abs($netAdjustment) : 0.0;

        return [
            'total_seconds' => $totalSeconds,
            'remaining_seconds' => $remainingSeconds,
            'remaining_ratio' => round($ratio, 6),
            'unused_current_credit' => $unusedCurrentCredit,
            'new_plan_charge' => $newPlanCharge,
            'net_adjustment' => $netAdjustment,
            'immediate_charge' => $immediateCharge,
            'credit_issued' => $creditIssued,
        ];
    }
}
