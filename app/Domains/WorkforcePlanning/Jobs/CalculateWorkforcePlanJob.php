<?php

namespace App\Domains\WorkforcePlanning\Jobs;

use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Services\HeadcountPlanningService;
use App\Domains\WorkforcePlanning\Services\LaborCostPlanningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateWorkforcePlanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $planId)
    {
    }

    public function handle(
        HeadcountPlanningService $headcountService,
        LaborCostPlanningService $costService
    ): void {
        $plan = HcmWorkforcePlan::find($this->planId);
        if (!$plan) {
            return;
        }

        // Initialize / re-calculate plan baseline
        $headcountService->initializeFromCoreHr($plan, $plan->department_id);
        $costService->calculateTotalLaborCost($plan->tenant_id, $plan->id);
    }
}
