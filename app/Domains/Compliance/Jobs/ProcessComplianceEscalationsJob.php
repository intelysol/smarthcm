<?php

namespace App\Domains\Compliance\Jobs;

use App\Domains\Compliance\Services\ComplianceAlertEscalationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessComplianceEscalationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId
    ) {}

    public function handle(ComplianceAlertEscalationService $service): void
    {
        $service->scanAndEscalate($this->tenantId);
    }
}
