<?php

namespace App\Domains\Career\Jobs;

use App\Domains\Career\Models\SuccessionPosition;
use App\Domains\Career\Services\SuccessionRiskEngine;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateSuccessionRiskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $positionId
    ) {}

    public function handle(SuccessionRiskEngine $riskEngine, ?TenantContext $tenantContext = null): void
    {
        $tenant = Tenant::query()->find($this->tenantId);
        if (! $tenant) return;
        if ($tenantContext && ! $tenantContext->id()) {
            $tenantContext->set($tenant);
        }

        $position = SuccessionPosition::query()->find($this->positionId);
        if ($position) {
            $riskEngine->recalculatePositionRisk($position);
        }
    }
}
