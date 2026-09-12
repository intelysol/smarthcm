<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Jobs;

use App\Domains\HealthSafety\Services\MedicalFitnessService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckMedicalFitnessExpirationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public int $daysAhead = 30
    ) {}

    public function handle(MedicalFitnessService $service): void
    {
        $expiringRecords = $service->getExpiringRecords($this->tenantId, $this->daysAhead);
        Log::info("Found {$expiringRecords->count()} medical fitness records expiring within {$this->daysAhead} days for tenant {$this->tenantId}.");
    }
}
