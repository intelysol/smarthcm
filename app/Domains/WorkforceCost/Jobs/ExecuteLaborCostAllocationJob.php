<?php

namespace App\Domains\WorkforceCost\Jobs;

use App\Domains\WorkforceCost\Models\HcmWorkforceCostLine;
use App\Domains\WorkforceCost\Services\LaborCostAllocationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExecuteLaborCostAllocationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public string $costLineId
    ) {}

    public function handle(LaborCostAllocationService $service): void
    {
        $line = HcmWorkforceCostLine::where('tenant_id', $this->tenantId)->find($this->costLineId);
        if ($line) {
            $service->allocateCostLine($line);
        }
    }
}