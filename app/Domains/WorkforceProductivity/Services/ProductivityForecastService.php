<?php

namespace App\Domains\WorkforceProductivity\Services;

use App\Domains\WorkforceProductivity\Contracts\ProductivityCalculationInterface;
use App\Domains\WorkforceProductivity\Models\HcmProductivityForecast;
use App\Domains\WorkforceProductivity\Models\HcmProductivityMeasurement;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProductivityForecastService
{
    public function __construct(
        protected ProductivityCalculationInterface $calculator
    ) {}

    /**
     * Generate forward-looking productivity forecast.
     */
    public function generateForecast(
        string $tenantId,
        ?string $departmentId,
        string $forecastStart,
        string $forecastEnd,
        array $assumptions = []
    ): HcmProductivityForecast {
        // Look up historical baseline measurements for this department or tenant
        $query = HcmProductivityMeasurement::where('tenant_id', $tenantId);
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        $historical = $query->latest('period_end')->take(3)->get();

        $baselineOutput = $historical->avg('output_volume') ?: 10000.0;
        $baselineHours = $historical->avg('productive_hours') ?: 1000.0;
        $baselineCost = $historical->avg('labor_cost') ?: 25000.0;

        $headcountGrowthPct = (float) ($assumptions['headcount_growth_pct'] ?? 0.0);
        $automationEfficiencyPct = (float) ($assumptions['automation_efficiency_pct'] ?? 0.0);
        $wageInflationPct = (float) ($assumptions['wage_inflation_pct'] ?? 3.0);

        // Project hours with headcount change
        $projectedHours = round($baselineHours * (1 + ($headcountGrowthPct / 100.0)), 2);
        // Project output including automation speed improvement
        $projectedOutput = round($baselineOutput * (1 + ($headcountGrowthPct / 100.0)) * (1 + ($automationEfficiencyPct / 100.0)), 4);
        // Project cost with wage inflation
        $projectedCost = round($baselineCost * (1 + ($headcountGrowthPct / 100.0)) * (1 + ($wageInflationPct / 100.0)), 4);

        $utilizationRate = (float) ($assumptions['expected_utilization_pct'] ?? 85.0);
        $costPerUnit = $this->calculator->calculateUnitCost($projectedCost, $projectedOutput);

        $forecastNumber = 'FCST-' . strtoupper(Str::random(8));

        return HcmProductivityForecast::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'forecast_number' => $forecastNumber,
            'department_id' => $departmentId,
            'forecast_start' => $forecastStart,
            'forecast_end' => $forecastEnd,
            'projected_output' => $projectedOutput,
            'projected_labor_hours' => round($projectedHours * 1.15, 2), // includes non-productive buffer
            'projected_productive_hours' => $projectedHours,
            'projected_utilization_rate' => $utilizationRate,
            'projected_labor_cost' => $projectedCost,
            'projected_cost_per_unit' => $costPerUnit,
            'assumptions' => array_merge($assumptions, [
                'baseline_output' => $baselineOutput,
                'baseline_hours' => $baselineHours,
                'baseline_cost' => $baselineCost,
            ]),
        ]);
    }
}
