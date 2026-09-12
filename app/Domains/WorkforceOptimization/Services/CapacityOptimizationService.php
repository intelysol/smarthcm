<?php

namespace App\Domains\WorkforceOptimization\Services;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationOpportunity;
use App\Domains\Organization\Models\Department;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CapacityOptimizationService
{
    /**
     * Compute capacity balancing across departments to eliminate over/under staffing.
     */
    public function analyzeCapacityRebalancing(?string $tenantId = null): array
    {
        $surplusDepartments = [];
        $deficitDepartments = [];

        // Aggregate capacity data from plans or headcount
        if (Schema::hasTable('hcm_workforce_capacity_plans')) {
            $plans = DB::table('hcm_workforce_capacity_plans')
                ->select('department_id', DB::raw('SUM(required_capacity_hours) as req'), DB::raw('SUM(available_capacity_hours) as avail'))
                ->groupBy('department_id')
                ->get();

            foreach ($plans as $p) {
                $net = (float) $p->avail - (float) $p->req;
                if ($net > 40) {
                    $surplusDepartments[] = [
                        'department_id' => $p->department_id,
                        'surplus_hours' => $net,
                    ];
                } elseif ($net < -40) {
                    $deficitDepartments[] = [
                        'department_id' => $p->department_id,
                        'deficit_hours' => abs($net),
                    ];
                }
            }
        }

        // Generate redeployment pairs (surplus -> deficit)
        $rebalancingPlans = [];
        foreach ($surplusDepartments as $surplus) {
            foreach ($deficitDepartments as $deficit) {
                $transferHours = min($surplus['surplus_hours'], $deficit['deficit_hours']);
                if ($transferHours > 0) {
                    $rebalancingPlans[] = [
                        'source_department_id' => $surplus['department_id'],
                        'target_department_id' => $deficit['department_id'],
                        'reallocated_hours' => $transferHours,
                        'estimated_savings' => $transferHours * 35.0, // External hiring avoided
                    ];
                }
            }
        }

        return [
            'surplus_departments' => $surplusDepartments,
            'deficit_departments' => $deficitDepartments,
            'rebalancing_recommendations' => $rebalancingPlans,
        ];
    }
}
