<?php

namespace App\Domains\WorkforceOptimization\Jobs;

use App\Domains\WorkforceOptimization\Services\SkillsOptimizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateSkillsOptimizationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $tenantId = null
    ) {}

    public function handle(SkillsOptimizationService $service): void
    {
        $service->analyzeSkillsHealth($this->tenantId);
    }
}
