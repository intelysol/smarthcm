<?php

namespace App\Domains\WorkforceProductivity\Jobs;

use App\Domains\WorkforceProductivity\Events\ProductivityVarianceDetected;
use App\Domains\WorkforceProductivity\Services\ProductivityVarianceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateProductivityVarianceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly array $baseline,
        public readonly array $comparison
    ) {}

    public function handle(ProductivityVarianceService $service): void
    {
        $variance = $service->calculateVariance($this->baseline, $this->comparison);
        event(new ProductivityVarianceDetected($this->tenantId, $variance));
    }
}
