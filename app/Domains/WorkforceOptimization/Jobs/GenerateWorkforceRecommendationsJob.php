<?php

namespace App\Domains\WorkforceOptimization\Jobs;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRun;
use App\Domains\WorkforceOptimization\Services\WorkforceRecommendationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateWorkforceRecommendationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public HcmWorkforceOptimizationRun $run,
        public array $options = []
    ) {}

    public function handle(WorkforceRecommendationService $service): void
    {
        $service->generateRecommendations($this->run, $this->options);
    }
}
