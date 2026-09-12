<?php

namespace App\Domains\WorkforceOptimization\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaborEfficiencyOptimizationService
{
    /**
     * Analyze labor efficiency and generate optimization opportunities for reducing overtime & contractor spend.
     */
    public function analyzeLaborEfficiencyOpportunities(?string $tenantId = null): array
    {
        $overtimeOpportunities = [];
        $contractorOpportunities = [];

        // 1. Analyze high overtime departments
        if (Schema::hasTable('hcm_department_productivity_aggregates')) {
            $highOt = DB::table('hcm_department_productivity_aggregates')
                ->where('overtime_hours', '>', 40)
                ->get();

            foreach ($highOt as $rec) {
                $potentialSavings = ($rec->overtime_hours * 0.5) * 55.0; // 50% shift rebalancing
                $overtimeOpportunities[] = [
                    'department_id' => $rec->department_id,
                    'current_ot_hours' => $rec->overtime_hours,
                    'target_rebalancing_hours' => round($rec->overtime_hours * 0.5, 1),
                    'projected_cost_savings' => round($potentialSavings, 2),
                    'recommended_action' => 'SHIFT_REBALANCING',
                ];
            }
        }

        // 2. Analyze contractor reliance
        if (Schema::hasTable('hcm_workforce_cost_summaries')) {
            $contractorSpend = DB::table('hcm_workforce_cost_summaries')
                ->where('contingent_cost', '>', 5000)
                ->get();

            foreach ($contractorSpend as $cs) {
                $contractorOpportunities[] = [
                    'department_id' => $cs->department_id ?? null,
                    'contingent_cost' => $cs->contingent_cost,
                    'potential_savings_via_permanent' => round($cs->contingent_cost * 0.35, 2),
                    'recommended_action' => 'HIRE_PERMANENT',
                ];
            }
        }

        return [
            'overtime_mitigations' => $overtimeOpportunities,
            'contractor_optimizations' => $contractorOpportunities,
        ];
    }
}
