<?php

namespace App\Domains\Analytics\Jobs;

use App\Domains\Analytics\Services\HcmWorkforceSnapshotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BuildWorkforceSnapshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public string $snapshotDate,
        public string $snapshotType = 'daily'
    ) {}

    public function handle(HcmWorkforceSnapshotService $snapshotService): void
    {
        $snapshotService->buildSnapshot($this->tenantId, $this->snapshotDate, $this->snapshotType);
    }
}
