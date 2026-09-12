<?php

namespace App\Domains\WorkforceProductivity\Jobs;

use App\Domains\WorkforceProductivity\Services\ProductivityUtilizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateUtilizationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly ?string $departmentId,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly float $availableCapacityHours,
        public readonly float $scheduledHours = 0.0
    ) {}

    public function handle(ProductivityUtilizationService $service): void
    {
        $service->calculateUtilization(
            $this->tenantId,
            $this->departmentId,
            $this->startDate,
            $this->endDate,
            $this->availableCapacityHours,
            $this->scheduledHours
        );
    }
}
