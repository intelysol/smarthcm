<?php

namespace App\Domains\WorkforcePlanning\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\WorkforcePlanning\Models\HcmWorkforceHeadcountPlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlanPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HeadcountPlanningService
{
    /**
     * Deterministically calculates monthly headcount projection for a department/branch.
     * Formula: Closing = Opening + Hires + Transfers In - Exits - Transfers Out.
     */
    public function calculateDeterministicBalance(
        int $opening,
        int $hires,
        int $transfersIn,
        int $exits,
        int $transfersOut
    ): int {
        return max(0, $opening + $hires + $transfersIn - $exits - $transfersOut);
    }

    public function recordHeadcountPeriod(
        HcmWorkforcePlan $plan,
        HcmWorkforcePlanPeriod $period,
        array $data
    ): HcmWorkforceHeadcountPlan {
        if ($plan->status === 'locked') {
            throw ValidationException::withMessages(['plan' => 'Cannot modify headcount in a locked plan.']);
        }

        $opening = (int) ($data['opening_headcount'] ?? 0);
        $hires = (int) ($data['planned_hires'] ?? 0);
        $transfersIn = (int) ($data['transfers_in'] ?? 0);
        $exits = (int) ($data['planned_exits'] ?? 0);
        $transfersOut = (int) ($data['transfers_out'] ?? 0);

        $closing = $this->calculateDeterministicBalance($opening, $hires, $transfersIn, $exits, $transfersOut);
        $plannedFte = (float) ($data['planned_fte'] ?? $closing);

        return HcmWorkforceHeadcountPlan::updateOrCreate(
            [
                'tenant_id' => $plan->tenant_id,
                'plan_id' => $plan->id,
                'period_id' => $period->id,
                'department_id' => $data['department_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
            ],
            [
                'opening_headcount' => $opening,
                'planned_hires' => $hires,
                'transfers_in' => $transfersIn,
                'planned_exits' => $exits,
                'transfers_out' => $transfersOut,
                'closing_headcount' => $closing,
                'planned_fte' => $plannedFte,
            ]
        );
    }

    /**
     * Initializes headcount plan periods for a department by populating opening from Core HR active count.
     */
    public function initializeFromCoreHr(HcmWorkforcePlan $plan, ?string $departmentId = null): array
    {
        $coreHrQuery = Employee::query()->where('tenant_id', $plan->tenant_id)->where('employment_status', 'active');
        if ($departmentId) {
            $coreHrQuery->where('department_id', $departmentId);
        }
        $currentActiveCount = $coreHrQuery->count();

        $periods = $plan->periods()->orderBy('period_sequence')->get();
        $runningOpening = $currentActiveCount;
        $results = [];

        foreach ($periods as $period) {
            $hires = 2; // Default planned additions
            $exits = 1; // Default planned attrition
            $closing = $this->calculateDeterministicBalance($runningOpening, $hires, 0, $exits, 0);

            $rec = HcmWorkforceHeadcountPlan::updateOrCreate(
                [
                    'tenant_id' => $plan->tenant_id,
                    'plan_id' => $plan->id,
                    'period_id' => $period->id,
                    'department_id' => $departmentId,
                ],
                [
                    'opening_headcount' => $runningOpening,
                    'planned_hires' => $hires,
                    'transfers_in' => 0,
                    'planned_exits' => $exits,
                    'transfers_out' => 0,
                    'closing_headcount' => $closing,
                    'planned_fte' => (float) $closing,
                ]
            );

            $results[] = $rec;
            $runningOpening = $closing;
        }

        return $results;
    }
}
