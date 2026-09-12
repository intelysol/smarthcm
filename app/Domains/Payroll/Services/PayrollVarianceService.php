<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Payroll\Models\PayrollCalculationSnapshot;
use App\Domains\Payroll\Models\PayrollPolicy;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Models\PayrollVariance;

class PayrollVarianceService
{
    /**
     * Compute variance against previous cycle for each employee in the run.
     */
    public function computeVariances(PayrollRun $run): array
    {
        $tenantId = $run->tenant_id;
        $period = $run->period;

        // Clear existing variances for this run
        $run->variances()->delete();

        // 1. Find previous payroll run in previous period
        $prevRun = PayrollRun::query()
            ->where('tenant_id', $tenantId)
            ->where('id', '!=', $run->id)
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('calculated_at')
            ->first();

        $policy = PayrollPolicy::query()->where('tenant_id', $tenantId)->where('is_active', true)->first();
        $thresholdPct = (float) ($policy?->variance_threshold_percentage ?? 10.00);

        $prevSnapshots = $prevRun
            ? PayrollCalculationSnapshot::query()->where('payroll_run_id', $prevRun->id)->get()->keyBy('employee_id')
            : collect();

        $variances = [];
        $currentSnapshots = $run->calculationSnapshots;

        foreach ($currentSnapshots as $cur) {
            $empId = $cur->employee_id;
            $prev = $prevSnapshots->get($empId);

            $curGross = (float) $cur->gross_pay;
            $prevGross = $prev ? (float) $prev->gross_pay : 0.0;
            $diffGross = $curGross - $prevGross;

            $pctDiff = $prevGross > 0 ? abs(($diffGross / $prevGross) * 100) : ($prev ? 0.0 : 100.0);

            $curNet = (float) $cur->net_pay;
            $prevNet = $prev ? (float) $prev->net_pay : 0.0;
            $diffNet = $curNet - $prevNet;

            $varianceType = 'normal';
            $isFlagged = false;
            $explanation = null;

            if (! $prev) {
                $varianceType = 'new_hire';
                $explanation = 'New employee in this payroll cycle.';
            } elseif ($pctDiff > $thresholdPct) {
                $isFlagged = true;
                $varianceType = $diffGross > 0 ? 'large_increase' : 'large_decrease';
                $explanation = sprintf('Gross pay changed by %.2f%% (exceeds %.1f%% threshold).', $pctDiff, $thresholdPct);
            }

            $variances[] = PayrollVariance::query()->create([
                'tenant_id' => $tenantId,
                'payroll_run_id' => $run->id,
                'employee_id' => $empId,
                'current_gross' => $curGross,
                'previous_gross' => $prevGross,
                'gross_variance_amount' => $diffGross,
                'gross_variance_percentage' => round($pctDiff, 2),
                'current_net' => $curNet,
                'previous_net' => $prevNet,
                'net_variance_amount' => $diffNet,
                'variance_type' => $varianceType,
                'is_flagged' => $isFlagged,
                'explanation' => $explanation,
            ]);
        }

        return $variances;
    }
}
