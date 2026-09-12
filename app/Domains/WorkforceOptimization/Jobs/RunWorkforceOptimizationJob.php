<?php

namespace App\Domains\WorkforceOptimization\Jobs;

use App\Domains\WorkforceOptimization\Contracts\WorkforceOptimizationInterface;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunWorkforceOptimizationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public ?string $modelCode = null,
        public array $scope = [],
        public ?User $triggeredBy = null
    ) {}

    public function handle(WorkforceOptimizationInterface $service): void
    {
        $service->runOptimization(
            tenantId: $this->tenantId,
            modelCode: $this->modelCode,
            scope: $this->scope,
            triggeredBy: $this->triggeredBy
        );
    }
}
