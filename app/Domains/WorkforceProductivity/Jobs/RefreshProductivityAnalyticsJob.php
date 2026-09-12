<?php

namespace App\Domains\WorkforceProductivity\Jobs;

use App\Domains\WorkforceProductivity\Services\ProductivitySnapshotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshProductivityAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $periodType,
        public readonly string $periodName,
        public readonly string $startDate,
        public readonly string $endDate
    ) {}

    public function handle(ProductivitySnapshotService $snapshotService): void
    {
        $snapshotService->buildSnapshot(
            $this->tenantId,
            $this->periodType,
            $this->periodName,
            $this->startDate,
            $this->endDate
        );
    }
}
