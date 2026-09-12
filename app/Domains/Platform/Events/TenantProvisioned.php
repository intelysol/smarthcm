<?php

namespace App\Domains\Platform\Events;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantProvisioned
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly string $tenantId, public readonly string $tenantUuid, public readonly Tenant $tenant) {}
}
