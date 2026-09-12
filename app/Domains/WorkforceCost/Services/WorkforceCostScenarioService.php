<?php

namespace App\Domains\WorkforceCost\Services;

use App\Domains\WorkforceCost\Models\HcmWorkforceCostScenario;
use Illuminate\Support\Str;

class WorkforceCostScenarioService
{
    /**
     * Calculates economic impact for what-if scenarios.
     */
    public function calculateScenario(
        string $tenantId,
        string $name,
        string $scenarioType,
        float $currentCost,
        array $parameters = [],
        ?int $userId = null
    ): HcmWorkforceCostScenario {
        $headcountDiff = (float) ($parameters['headcount_difference'] ?? 0);
        $fteDiff = (float) ($parameters['fte_difference'] ?? $headcountDiff);
        $capacityHoursDiff = (float) ($parameters['capacity_difference_hours'] ?? ($fteDiff * 160.0));

        $costDiff = 0.0;
        $roi = null;
        $payback = null;

        switch ($scenarioType) {
            case 'hire':
                $avgSalary = (float) ($parameters['average_salary'] ?? 60000.00);
                $costDiff = round($fteDiff * $avgSalary, 4);
                break;
            case 'freeze':
                $savedHires = (float) ($parameters['prevented_hires'] ?? 5);
                $avgSalary = (float) ($parameters['average_salary'] ?? 60000.00);
                $costDiff = -round($savedHires * $avgSalary, 4);
                break;
            case 'contractor_substitution':
                $contractorRate = (float) ($parameters['contractor_hourly_rate'] ?? 75.00);
                $employeeHourlyCost = (float) ($parameters['employee_hourly_cost'] ?? 50.00);
                $hours = (float) ($parameters['annual_hours'] ?? 2000.00);
                $costDiff = round(($contractorRate - $employeeHourlyCost) * $hours, 4);
                break;
            case 'automate':
                $investmentCost = (float) ($parameters['investment_cost'] ?? 100000.00);
                $annualLaborSaving = (float) ($parameters['annual_labor_saving'] ?? 150000.00);
                $costDiff = -round($annualLaborSaving, 4);
                $roi = round((($annualLaborSaving - $investmentCost) / $investmentCost) * 100, 2);
                $payback = round(($investmentCost / $annualLaborSaving) * 12, 1);
                break;
            default:
                $costDiff = (float) ($parameters['custom_cost_difference'] ?? 0.0);
        }

        $scenarioCost = round($currentCost + $costDiff, 4);

        return HcmWorkforceCostScenario::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'name' => $name,
            'scenario_type' => $scenarioType,
            'current_cost' => $currentCost,
            'scenario_cost' => $scenarioCost,
            'cost_difference' => $costDiff,
            'headcount_difference' => $headcountDiff,
            'fte_difference' => $fteDiff,
            'capacity_difference_hours' => $capacityHoursDiff,
            'estimated_roi_percentage' => $roi,
            'payback_months' => $payback,
            'status' => 'active',
            'currency' => $parameters['currency'] ?? 'USD',
            'parameters' => $parameters,
            'created_by' => $userId,
        ]);
    }
}