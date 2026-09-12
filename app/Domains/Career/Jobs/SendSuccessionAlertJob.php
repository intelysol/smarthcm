<?php

namespace App\Domains\Career\Jobs;

use App\Domains\Career\Models\SuccessionPosition;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSuccessionAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly string $tenantId) {}

    public function handle(?TenantContext $tenantContext = null): void
    {
        $tenant = Tenant::query()->find($this->tenantId);
        if (! $tenant) return;
        if ($tenantContext && ! $tenantContext->id()) {
            $tenantContext->set($tenant);
        }

        $criticalPositions = SuccessionPosition::query()
            ->where('tenant_id', $this->tenantId)
            ->where('risk_score', '>=', 60.0)
            ->with(['position', 'incumbent'])
            ->get();

        Log::info("Succession Risk Alert: {$criticalPositions->count()} critical positions need successor coverage in tenant {$this->tenantId}");
    }
}
