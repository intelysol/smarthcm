<?php

namespace App\Domains\WorkforceAdmin\Jobs;

use App\Domains\WorkforceAdmin\Services\HrDataQualityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunDataQualityScanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId
    ) {}

    public function handle(HrDataQualityService $service): void
    {
        $service->runQualityScan($this->tenantId);
    }
}
