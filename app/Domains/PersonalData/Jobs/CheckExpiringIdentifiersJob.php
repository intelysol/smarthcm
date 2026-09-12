<?php

namespace App\Domains\PersonalData\Jobs;

use App\Domains\PersonalData\Services\EmployeeIdentifierService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckExpiringIdentifiersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $tenantId = null,
        public int $days = 30
    ) {}

    public function handle(EmployeeIdentifierService $service): void
    {
        $tenantIds = $this->tenantId ? [$this->tenantId] : Tenant::pluck('id')->toArray();

        foreach ($tenantIds as $tId) {
            // Evaluates expiring IDs for alerting / logging
            $service->getExpiringIdentifiers($tId, $this->days);
        }
    }
}
