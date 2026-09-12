<?php

namespace App\Domains\Compliance\Jobs;

use App\Domains\Compliance\Services\ComplianceAlertEscalationService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckComplianceExpirationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $tenantId = null
    ) {}

    public function handle(ComplianceAlertEscalationService $service): void
    {
        $tenantIds = $this->tenantId ? [$this->tenantId] : Tenant::pluck('id')->toArray();

        foreach ($tenantIds as $tId) {
            $service->scanAndEscalate($tId);
        }
    }
}
