<?php

namespace App\Domains\WorkforcePlanning\Services;

use App\Domains\WorkforcePlanning\Models\HcmWorkforceCostPlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use Illuminate\Support\Facades\DB;

class LaborCostPlanningService
{
    public const COST_CATEGORIES = [
        'base_salary',
        'overtime',
        'bonus',
        'commission',
        'benefits',
        'statutory_taxes',
        'recruitment',
        'training',
        'relocation',
    ];

    public function recordCostPlan(HcmWorkforcePlan $plan, array $data): HcmWorkforceCostPlan
    {
        $budgeted = (float) ($data['budgeted_amount'] ?? 0.00);
        $forecast = (float) ($data['forecast_amount'] ?? 0.00);
        $actual = (float) ($data['actual_amount'] ?? 0.00);
        $variance = round($budgeted - $actual, 2);

        return HcmWorkforceCostPlan::updateOrCreate(
            [
                'tenant_id' => $plan->tenant_id,
                'plan_id' => $plan->id,
                'department_id' => $data['department_id'] ?? null,
                'cost_category' => $data['cost_category'],
            ],
            [
                'budgeted_amount' => $budgeted,
                'forecast_amount' => $forecast,
                'actual_amount' => $actual,
                'variance_amount' => $variance,
                'currency' => $data['currency'] ?? $plan->currency,
            ]
        );
    }

    public function calculateTotalLaborCost(string $tenantId, string $planId): array
    {
        $costPlans = HcmWorkforceCostPlan::where('tenant_id', $tenantId)->where('plan_id', $planId)->get();

        $totalBudgeted = (float) $costPlans->sum('budgeted_amount');
        $totalForecast = (float) $costPlans->sum('forecast_amount');
        $totalActual = (float) $costPlans->sum('actual_amount');
        $totalVariance = round($totalBudgeted - $totalActual, 2);

        $byCategory = [];
        foreach (self::COST_CATEGORIES as $cat) {
            $catRecords = $costPlans->where('cost_category', $cat);
            $byCategory[$cat] = [
                'budgeted' => (float) $catRecords->sum('budgeted_amount'),
                'forecast' => (float) $catRecords->sum('forecast_amount'),
                'actual' => (float) $catRecords->sum('actual_amount'),
                'variance' => round((float) $catRecords->sum('budgeted_amount') - (float) $catRecords->sum('actual_amount'), 2),
            ];
        }

        return [
            'total_budgeted' => round($totalBudgeted, 2),
            'total_forecast' => round($totalForecast, 2),
            'total_actual' => round($totalActual, 2),
            'total_variance' => $totalVariance,
            'by_category' => $byCategory,
        ];
    }
}
