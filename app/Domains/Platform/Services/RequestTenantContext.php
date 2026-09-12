<?php

namespace App\Domains\Platform\Services;

use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use LogicException;

class RequestTenantContext implements TenantContext
{
    private ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        if ($this->tenant !== null && ! $this->tenant->is($tenant)) {
            throw new LogicException('Tenant context is immutable after resolution.');
        }
        $this->tenant = $tenant;
    }

    public function current(): ?Tenant
    {
        return $this->tenant;
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?string
    {
        return $this->tenant?->getKey();
    }

    public function uuid(): ?string
    {
        return $this->tenant?->uuid ?? $this->tenant?->getKey();
    }

    public function user(): ?User
    {
        return request()->user();
    }

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    public function clear(): void
    {
        $this->tenant = null;
    }
}
