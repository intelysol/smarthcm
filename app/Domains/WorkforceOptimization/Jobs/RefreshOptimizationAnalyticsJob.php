<?php

namespace App\Domains\WorkforceOptimization\Jobs;

use App\Domains\WorkforceOptimization\Contracts\WorkforceOptimizationInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshOptimizationAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $tenantId = null
    ) {}

    public function handle(WorkforceOptimizationInterface $service): void
    {
        if ($this->tenantId) {
            $service->runOptimization($this->tenantId);
        }
    }
}
