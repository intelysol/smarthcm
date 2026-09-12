<?php

namespace App\Domains\WorkforceOptimization\Jobs;

use App\Domains\WorkforceOptimization\Services\WorkforceOpportunityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DetectWorkforceOpportunitiesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $tenantId = null,
        public array $filters = []
    ) {}

    public function handle(WorkforceOpportunityService $service): void
    {
        $service->detectOpportunities($this->tenantId, $this->filters);
    }
}
