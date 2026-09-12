<?php

namespace App\Domains\Organization\Repositories;

use App\Domains\Organization\Models\Company;

interface CompanyRepositoryInterface
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Company;

    public function existsForTenantByName(string $tenantId, string $name): bool;
}
