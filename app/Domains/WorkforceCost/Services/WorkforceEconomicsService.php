<?php

namespace App\Domains\WorkforceCost\Services;

use App\Domains\WorkforceCost\Models\HcmWorkforceCostEconomics;
use App\Domains\WorkforceCost\Models\HcmWorkforceCostLine;
use Carbon\Carbon;
use Illuminate\Support\Str;

class WorkforceEconomicsService
{
    /**
     * Computes workforce economics metrics.
     */
    public function calculateEconomics(
        string $tenantId,
        Carbon $start,
        Carbon $end,
        ?string $departmentId = null
    ): HcmWorkforceCostEconomics {
        $linesQuery = HcmWorkforceCostLine::where('tenant_id', $tenantId)
            ->whereDate('cost_date', '>=', $start->toDateString())
            ->whereDate('cost_date', '<=', $end->toDateString());

        if ($departmentId) {
            $linesQuery->where('department_id', $departmentId);
        }

        $totalCost = (float) $linesQuery->sum('amount');
        $laborHours = (float) $linesQuery->sum('hours_worked');
        $headcount = max(1, (int) (clone $linesQuery)->distinct('employee_id')->count('employee_id'));
        $fte = (float) $headcount;

        $overtimeCost = (float) (clone $linesQuery)->where('component_type', 'OVERTIME')->sum('amount');
        $contractorCost = (float) (clone $linesQuery)->where('cost_category', 'contractor')->sum('amount');
        $absenceCost = (float) (clone $linesQuery)->where('component_type', 'ABSENCE')->sum('amount');
        $vacancyCost = (float) (clone $linesQuery)->where('component_type', 'VACANCY')->sum('amount');

        // Rates
        $costPerFte = round($totalCost / $fte, 2);
        $costPerEmployee = round($totalCost / $headcount, 2);
        $effectiveHours = $laborHours > 0 ? $laborHours : ($fte * 160.0);
        $costPerLaborHour = round($totalCost / $effectiveHours, 4);
        $costPerProductiveHour = round($totalCost / ($effectiveHours * 0.85), 4); // 85% productive time benchmark

        $overtimeRatio = $totalCost > 0 ? round(($overtimeCost / $totalCost) * 100, 2) : 0.00;
        $contractorRatio = $totalCost > 0 ? round(($contractorCost / $totalCost) * 100, 2) : 0.00;

        return HcmWorkforceCostEconomics::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'department_id' => $departmentId,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'cost_per_fte' => $costPerFte,
            'cost_per_employee' => $costPerEmployee,
            'cost_per_labor_hour' => $costPerLaborHour,
            'cost_per_productive_hour' => $costPerProductiveHour,
            'overtime_cost_ratio' => $overtimeRatio,
            'contractor_ratio' => $contractorRatio,
            'absence_cost_total' => $absenceCost,
            'vacancy_cost_total' => $vacancyCost,
            'cost_per_available_capacity_hour' => $costPerLaborHour,
            'cost_per_required_capacity_hour' => round($costPerLaborHour * 1.08, 4),
            'currency' => 'USD',
        ]);
    }
}