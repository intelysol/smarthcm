<?php

namespace App\Domains\Analytics\Jobs;

use App\Domains\Analytics\Services\HcmAnalyticsAlertService;
use App\Domains\Analytics\Services\HcmWorkforceAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EvaluateAnalyticsAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId
    ) {}

    public function handle(HcmAnalyticsAlertService $alertService, HcmWorkforceAnalyticsService $workforceService): void
    {
        $headcount = $workforceService->getHeadcountSummary($this->tenantId);
        $alertService->evaluateAlerts($this->tenantId, [
            'HCM_HEADCOUNT_ACTIVE' => $headcount['active_headcount'],
            'HCM_TURNOVER_RATE' => 8.5,
        ]);
    }
}
