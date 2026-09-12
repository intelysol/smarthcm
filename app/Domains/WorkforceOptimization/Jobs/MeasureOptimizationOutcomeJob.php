<?php

namespace App\Domains\WorkforceOptimization\Jobs;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRecommendation;
use App\Domains\WorkforceOptimization\Services\OptimizationOutcomeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MeasureOptimizationOutcomeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public HcmWorkforceOptimizationRecommendation $recommendation,
        public string $metricCategory,
        public string $metricName,
        public float $baselineValue,
        public float $actualValue,
        public int $windowDays = 30
    ) {}

    public function handle(OptimizationOutcomeService $service): void
    {
        $service->measureOutcome(
            recommendation: $this->recommendation,
            metricCategory: $this->metricCategory,
            metricName: $this->metricName,
            baselineValue: $this->baselineValue,
            actualValue: $this->actualValue,
            windowDays: $this->windowDays
        );
    }
}
