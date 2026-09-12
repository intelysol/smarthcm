<?php

namespace App\Domains\Platform\Services;

use App\Domains\Platform\Enums\TenantStatus;
use App\Domains\Platform\Models\TenantAuditLog;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use DomainException;

class TenantLifecycleService
{
    public function transition(Tenant $tenant, TenantStatus $to, ?User $actor, ?string $reason = null): Tenant
    {
        $from = $tenant->status instanceof TenantStatus ? $tenant->status : TenantStatus::from((string) $tenant->status);
        if (! in_array($to, $from->transitions(), true)) {
            throw new DomainException("Transition from {$from->value} to {$to->value} is not allowed.");
        }
        $tenant->forceFill(['status' => $to, 'is_active' => $to->isAccessible(), 'activated_at' => $to === TenantStatus::Active ? now() : $tenant->activated_at, 'suspended_at' => $to === TenantStatus::Suspended ? now() : null, 'updated_by' => $actor?->id, 'version' => $tenant->version + 1])->save();
        TenantAuditLog::query()->create(['tenant_id' => $tenant->id, 'actor_id' => $actor?->id, 'action' => 'tenant.lifecycle.transition', 'metadata' => ['from' => $from->value, 'to' => $to->value, 'reason' => $reason], 'occurred_at' => now()]);

        return $tenant->refresh();
    }
}
