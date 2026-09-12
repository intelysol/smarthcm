<?php

namespace App\Domains\WorkforcePlanning\Services;

use App\Domains\WorkforcePlanning\Models\HcmWorkforceCapacityPlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforceDemandPlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforceSupplyPlan;
use Illuminate\Support\Facades\DB;

class WorkforceDemandSupplyService
{
    public function recordDemandPlan(HcmWorkforcePlan $plan, array $data): HcmWorkforceDemandPlan
    {
        $currentFte = (float) ($data['current_fte'] ?? 0.00);
        $requiredFte = (float) ($data['required_fte'] ?? 0.00);
        $gapFte = round($requiredFte - $currentFte, 2);

        return HcmWorkforceDemandPlan::updateOrCreate(
            [
                'tenant_id' => $plan->tenant_id,
                'plan_id' => $plan->id,
                'department_id' => $data['department_id'] ?? null,
                'job_grade_id' => $data['job_grade_id'] ?? null,
            ],
            [
                'job_family' => $data['job_family'] ?? null,
                'driver_name' => $data['driver_name'] ?? 'business_growth',
                'current_fte' => $currentFte,
                'required_fte' => $requiredFte,
                'demand_gap_fte' => $gapFte,
                'driver_inputs' => $data['driver_inputs'] ?? null,
                'justification' => $data['justification'] ?? null,
            ]
        );
    }

    public function recordSupplyPlan(HcmWorkforcePlan $plan, array $data): HcmWorkforceSupplyPlan
    {
        $currentHeadcount = (int) ($data['current_headcount'] ?? 0);
        $retirements = (int) ($data['expected_retirements'] ?? 0);
        $attrition = (int) ($data['expected_attrition'] ?? 0);
        $transfersOut = (int) ($data['expected_transfers_out'] ?? 0);
        $transfersIn = (int) ($data['expected_transfers_in'] ?? 0);
        $internalHires = (int) ($data['expected_internal_hires'] ?? 0);
        $externalHiringRequired = (int) ($data['external_hiring_required'] ?? 0);

        // Projected closing = current - retirements - attrition - transfersOut + transfersIn + internalHires + externalHiring
        $projectedClosing = max(0, $currentHeadcount - $retirements - $attrition - $transfersOut + $transfersIn + $internalHires + $externalHiringRequired);

        return HcmWorkforceSupplyPlan::updateOrCreate(
            [
                'tenant_id' => $plan->tenant_id,
                'plan_id' => $plan->id,
                'department_id' => $data['department_id'] ?? null,
            ],
            [
                'current_headcount' => $currentHeadcount,
                'expected_retirements' => $retirements,
                'expected_attrition' => $attrition,
                'expected_transfers_out' => $transfersOut,
                'expected_transfers_in' => $transfersIn,
                'expected_internal_hires' => $internalHires,
                'external_hiring_required' => $externalHiringRequired,
                'projected_closing_headcount' => $projectedClosing,
            ]
        );
    }

    public function recordCapacityPlan(HcmWorkforcePlan $plan, array $data): HcmWorkforceCapacityPlan
    {
        $volume = (float) ($data['workload_volume'] ?? 0.00);
        $ratio = (float) ($data['ratio_per_fte'] ?? 1.00);
        $calculatedRequiredFte = $ratio > 0 ? round($volume / $ratio, 2) : 0.00;

        return HcmWorkforceCapacityPlan::updateOrCreate(
            [
                'tenant_id' => $plan->tenant_id,
                'plan_id' => $plan->id,
                'capacity_metric_name' => $data['capacity_metric_name'],
            ],
            [
                'workload_volume' => $volume,
                'ratio_per_fte' => $ratio,
                'calculated_required_fte' => $calculatedRequiredFte,
            ]
        );
    }

    public function getWorkforceGapAnalysis(string $tenantId, string $planId): array
    {
        $demandPlans = HcmWorkforceDemandPlan::where('tenant_id', $tenantId)->where('plan_id', $planId)->get();
        $supplyPlans = HcmWorkforceSupplyPlan::where('tenant_id', $tenantId)->where('plan_id', $planId)->get();

        $totalDemandFte = (float) $demandPlans->sum('required_fte');
        $totalCurrentFte = (float) $demandPlans->sum('current_fte');
        $totalDemandGapFte = (float) $demandPlans->sum('demand_gap_fte');

        $totalSupplyProjected = (int) $supplyPlans->sum('projected_closing_headcount');
        $totalCurrentHeadcount = (int) $supplyPlans->sum('current_headcount');
        $totalRetirements = (int) $supplyPlans->sum('expected_retirements');
        $totalAttrition = (int) $supplyPlans->sum('expected_attrition');
        $totalExternalHiringNeeded = (int) $supplyPlans->sum('external_hiring_required');

        return [
            'total_current_headcount' => $totalCurrentHeadcount,
            'total_demand_fte' => round($totalDemandFte, 2),
            'total_current_fte' => round($totalCurrentFte, 2),
            'total_demand_gap_fte' => round($totalDemandGapFte, 2),
            'total_projected_supply' => $totalSupplyProjected,
            'total_expected_retirements' => $totalRetirements,
            'total_expected_attrition' => $totalAttrition,
            'total_external_hiring_needed' => $totalExternalHiringNeeded,
            'net_workforce_gap' => round($totalDemandFte - $totalSupplyProjected, 2),
        ];
    }
}
