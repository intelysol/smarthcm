<?php

namespace App\Domains\WorkforcePlanning\Jobs;

use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Services\LaborCostPlanningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateLaborCostForecastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $planId)
    {
    }

    public function handle(LaborCostPlanningService $laborCostService): void
    {
        $plan = HcmWorkforcePlan::find($this->planId);
        if ($plan) {
            $laborCostService->calculateTotalLaborCost($plan->tenant_id, $plan->id);
        }
    }
}
