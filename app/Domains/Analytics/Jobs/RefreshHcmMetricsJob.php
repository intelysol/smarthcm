<?php

namespace App\Domains\Analytics\Jobs;

use App\Domains\Analytics\Services\HcmWorkforceAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshHcmMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId
    ) {}

    public function handle(HcmWorkforceAnalyticsService $workforceService): void
    {
        $workforceService->getHeadcountSummary($this->tenantId);
    }
}
