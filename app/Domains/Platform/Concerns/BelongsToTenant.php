<?php

namespace App\Domains\Platform\Concerns;

use App\Domains\Platform\Contracts\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $tenantId = app(TenantContext::class)->id();
            if ($tenantId !== null) {
                $builder->where($builder->qualifyColumn('tenant_id'), $tenantId);
            }
        });

        static::creating(function (Model $model): void {
            $tenantId = app(TenantContext::class)->id() ?? $model->getAttribute('tenant_id');
            if ($tenantId === null) {
                throw new LogicException('A tenant context is required to create tenant-owned records.');
            }
            $model->setAttribute('tenant_id', $tenantId);
        });
    }
}
