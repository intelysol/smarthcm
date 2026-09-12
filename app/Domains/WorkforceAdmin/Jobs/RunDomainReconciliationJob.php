<?php

namespace App\Domains\WorkforceAdmin\Jobs;

use App\Domains\WorkforceAdmin\Models\OpsReconciliationRule;
use App\Domains\WorkforceAdmin\Services\CrossDomainReconciliationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunDomainReconciliationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public OpsReconciliationRule $rule
    ) {}

    public function handle(CrossDomainReconciliationService $service): void
    {
        $service->reconcile($this->rule);
    }
}
