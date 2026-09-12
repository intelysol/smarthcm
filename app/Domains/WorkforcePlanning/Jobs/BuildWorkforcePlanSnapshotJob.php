<?php

namespace App\Domains\WorkforcePlanning\Jobs;

use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BuildWorkforcePlanSnapshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $planId, public ?int $userId = null)
    {
    }

    public function handle(WorkforcePlanService $planService): void
    {
        $plan = HcmWorkforcePlan::find($this->planId);
        if ($plan && $plan->status !== 'locked') {
            $planService->lockPlan($plan, $this->userId);
        }
    }
}
