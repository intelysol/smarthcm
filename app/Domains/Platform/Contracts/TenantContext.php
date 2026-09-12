<?php

namespace App\Domains\Platform\Contracts;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;

interface TenantContext
{
    public function current(): ?Tenant;

    public function set(Tenant $tenant): void;

    public function tenant(): ?Tenant;

    public function id(): ?string;

    public function uuid(): ?string;

    public function user(): ?User;

    public function hasTenant(): bool;

    public function clear(): void;
}
