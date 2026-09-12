<?php

namespace App\Domains\WorkforceCost\Jobs;

use App\Domains\WorkforceCost\Services\WorkforceCostReconciliationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReconcileWorkforceCostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public string $payrollRunId
    ) {}

    public function handle(WorkforceCostReconciliationService $service): void
    {
        $service->reconcileWithPayroll($this->tenantId, $this->payrollRunId);
    }
}