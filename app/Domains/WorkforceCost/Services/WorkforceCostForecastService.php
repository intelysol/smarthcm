<?php

namespace App\Domains\WorkforceCost\Services;

use App\Domains\WorkforceCost\Models\HcmWorkforceCostForecast;
use App\Domains\WorkforceCost\Models\HcmWorkforceCostLine;
use Carbon\Carbon;
use Illuminate\Support\Str;

class WorkforceCostForecastService
{
    /**
     * Generate forward-looking workforce cost forecast.
     */
    public function generateForecast(
        string $tenantId,
        Carbon $forecastStart,
        Carbon $forecastEnd,
        ?string $departmentId = null,
        array $assumptions = []
    ): HcmWorkforceCostForecast {
        // Baseline from prior 30 days
        $historyQuery = HcmWorkforceCostLine::where('tenant_id', $tenantId)
            ->whereDate('cost_date', '>=', $forecastStart->copy()->subMonth()->toDateString())
            ->whereDate('cost_date', '<', $forecastStart->toDateString());

        if ($departmentId) {
            $historyQuery->where('department_id', $departmentId);
        }

        $baseline = (float) $historyQuery->sum('amount');
        if ($baseline <= 0) {
            $baseline = (float) ($assumptions['default_baseline'] ?? 50000.00);
        }

        // Apply assumptions
        $headcountGrowthPct = (float) ($assumptions['headcount_growth_percent'] ?? 2.5); // e.g. 2.5%
        $salaryIncreasePct = (float) ($assumptions['salary_increase_percent'] ?? 3.0); // e.g. 3%
        $overtimeFactorPct = (float) ($assumptions['overtime_factor_percent'] ?? 1.0); // e.g. 1%
        $contractorImpactPct = (float) ($assumptions['contractor_factor_percent'] ?? 0.5);

        $hcImpact = round($baseline * ($headcountGrowthPct / 100), 4);
        $salaryImpact = round($baseline * ($salaryIncreasePct / 100), 4);
        $otImpact = round($baseline * ($overtimeFactorPct / 100), 4);
        $contractorImpact = round($baseline * ($contractorImpactPct / 100), 4);

        $forecastTotal = $baseline + $hcImpact + $salaryImpact + $otImpact + $contractorImpact;

        return HcmWorkforceCostForecast::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'department_id' => $departmentId,
            'forecast_period_start' => $forecastStart->toDateString(),
            'forecast_period_end' => $forecastEnd->toDateString(),
            'baseline_amount' => $baseline,
            'headcount_impact_amount' => $hcImpact,
            'salary_increase_impact_amount' => $salaryImpact,
            'overtime_impact_amount' => $otImpact,
            'contractor_impact_amount' => $contractorImpact,
            'forecast_total_cost' => $forecastTotal,
            'currency' => $assumptions['currency'] ?? 'USD',
            'assumptions' => array_merge([
                'headcount_growth_percent' => $headcountGrowthPct,
                'salary_increase_percent' => $salaryIncreasePct,
                'overtime_factor_percent' => $overtimeFactorPct,
                'contractor_factor_percent' => $contractorImpactPct,
            ], $assumptions),
        ]);
    }
}