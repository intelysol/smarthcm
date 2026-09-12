<?php

namespace App\Domains\WorkforceProductivity\Jobs;

use App\Domains\WorkforceProductivity\Events\ProductivitySnapshotGenerated;
use App\Domains\WorkforceProductivity\Services\ProductivitySnapshotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BuildProductivitySnapshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $periodType,
        public readonly string $periodName,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly ?string $idempotencyKey = null,
        public readonly ?int $userId = null
    ) {}

    public function handle(ProductivitySnapshotService $service): void
    {
        $snapshot = $service->buildSnapshot(
            $this->tenantId,
            $this->periodType,
            $this->periodName,
            $this->startDate,
            $this->endDate,
            $this->idempotencyKey,
            $this->userId
        );

        event(new ProductivitySnapshotGenerated($snapshot));
    }
}
