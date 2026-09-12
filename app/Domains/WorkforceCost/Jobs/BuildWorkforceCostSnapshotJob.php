<?php

namespace App\Domains\WorkforceCost\Jobs;

use App\Domains\WorkforceCost\Services\LaborCostAggregationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BuildWorkforceCostSnapshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public string $periodName,
        public string $startDate,
        public string $endDate,
        public string $currency = 'USD',
        public ?int $userId = null
    ) {}

    public function handle(LaborCostAggregationService $service): void
    {
        $service->buildSnapshot(
            $this->tenantId,
            $this->periodName,
            Carbon::parse($this->startDate),
            Carbon::parse($this->endDate),
            $this->currency,
            $this->userId
        );
    }
}