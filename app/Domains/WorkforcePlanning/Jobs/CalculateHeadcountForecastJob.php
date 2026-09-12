<?php

namespace App\Domains\WorkforcePlanning\Jobs;

use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Services\ActualVsPlanAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateHeadcountForecastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $planId)
    {
    }

    public function handle(ActualVsPlanAnalyticsService $analyticsService): void
    {
        $plan = HcmWorkforcePlan::find($this->planId);
        if ($plan) {
            $analyticsService->getActualVsPlanMatrix($plan);
        }
    }
}
