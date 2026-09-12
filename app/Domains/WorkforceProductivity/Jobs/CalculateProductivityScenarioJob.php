<?php

namespace App\Domains\WorkforceProductivity\Jobs;

use App\Domains\WorkforceProductivity\Events\ProductivityScenarioCalculated;
use App\Domains\WorkforceProductivity\Services\ProductivityScenarioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateProductivityScenarioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $scenarioName,
        public readonly string $scenarioType,
        public readonly ?string $baselineSnapshotId,
        public readonly int $headcountDelta,
        public readonly float $avgCostPerHead,
        public readonly float $avgMonthlyHoursPerHead,
        public readonly float $baselineHourlyOutputRate,
        public readonly float $valuePerUnit,
        public readonly array $assumptions = []
    ) {}

    public function handle(ProductivityScenarioService $service): void
    {
        $scenario = $service->simulateScenario(
            $this->tenantId,
            $this->scenarioName,
            $this->scenarioType,
            $this->baselineSnapshotId,
            $this->headcountDelta,
            $this->avgCostPerHead,
            $this->avgMonthlyHoursPerHead,
            $this->baselineHourlyOutputRate,
            $this->valuePerUnit,
            $this->assumptions
        );

        event(new ProductivityScenarioCalculated($scenario));
    }
}
