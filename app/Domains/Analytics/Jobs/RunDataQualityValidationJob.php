<?php

namespace App\Domains\Analytics\Jobs;

use App\Domains\Analytics\Services\HcmDataQualityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunDataQualityValidationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId
    ) {}

    public function handle(HcmDataQualityService $qualityService): void
    {
        $qualityService->runDataQualityValidation($this->tenantId);
    }
}
