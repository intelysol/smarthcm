<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Jobs;

use App\Domains\HealthSafety\Services\CorrectiveActionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOverdueCorrectiveActionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId
    ) {}

    public function handle(CorrectiveActionService $service): void
    {
        $overdueActions = $service->getOverdueActions($this->tenantId);
        Log::info("Found {$overdueActions->count()} overdue safety corrective actions for tenant {$this->tenantId}.");
    }
}
