<?php

namespace App\Domains\Organization\Repositories;

use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Repositories\BaseRepository;

class EloquentCompanyRepository extends BaseRepository implements CompanyRepositoryInterface
{
    protected function modelClass(): string
    {
        return Company::class;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Company
    {
        /** @var Company $company */
        $company = parent::create($attributes);

        return $company;
    }

    public function existsForTenantByName(string $tenantId, string $name): bool
    {
        return $this->query()
            ->where('tenant_id', $tenantId)
            ->where('name', $name)
            ->exists();
    }
}
