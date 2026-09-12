<?php

namespace App\Domains\Platform\Services;

use App\Domains\Platform\Enums\TenantStatus;
use App\Domains\Platform\Events\TenantProvisioned;
use App\Domains\Platform\Models\TenantBranding;
use App\Domains\Platform\Models\TenantFeature;
use App\Domains\Platform\Models\TenantSetting;
use App\Domains\Platform\Models\TenantLocalization;
use App\Domains\Platform\Models\TenantSecurityPolicy;
use App\Domains\Platform\Models\TenantUsage;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TenantProvisioningService
{
    /** @param array<string, mixed> $attributes */
    public function provision(array $attributes, ?User $actor = null): Tenant
    {
        return DB::transaction(function () use ($attributes, $actor): Tenant {
            $tenant = Tenant::query()->firstOrCreate(
                ['slug' => $attributes['slug']],
                [...$attributes, 'uuid' => $attributes['uuid'] ?? (string) \Illuminate\Support\Str::uuid(), 'tenant_code' => $attributes['tenant_code'] ?? strtoupper($attributes['slug']), 'status' => TenantStatus::Pending, 'is_active' => false, 'created_by' => $actor?->id],
            );
            TenantSetting::query()->firstOrCreate(['tenant_id' => $tenant->id], ['configuration' => ['name' => $tenant->name, 'timezone' => $tenant->timezone, 'locale' => $tenant->locale, 'currency' => $tenant->currency]]);
            TenantBranding::query()->firstOrCreate(['tenant_id' => $tenant->id], ['configuration' => ['logo' => $tenant->logo, 'primary_color' => null, 'secondary_color' => null]]);
            TenantFeature::query()->firstOrCreate(['tenant_id' => $tenant->id], ['configuration' => []]);
            TenantLocalization::query()->firstOrCreate(['tenant_id' => $tenant->id], ['configuration' => ['locale' => $tenant->locale, 'timezone' => $tenant->timezone, 'currency' => $tenant->currency]]);
            TenantSecurityPolicy::query()->firstOrCreate(['tenant_id' => $tenant->id], ['configuration' => []]);
            TenantUsage::query()->firstOrCreate(['tenant_id' => $tenant->id], ['metrics' => []]);
            if ($actor !== null) {
                $tenant->members()->syncWithoutDetaching([$actor->id => ['status' => 'active', 'role_placeholder' => 'administrator', 'joined_at' => now()]]);
            }
            TenantProvisioned::dispatch($tenant->id, $tenant->uuid ?? $tenant->id, $tenant);

            return $tenant;
        });
    }
}
