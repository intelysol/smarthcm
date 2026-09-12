<?php

namespace App\Domains\Platform\Repositories;

use App\Domains\Platform\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoleRepository
{
    public function paginate(string $tenantId, array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 25), 1), 100);
        $sort = in_array($filters['sort'] ?? 'name', ['name', 'label', 'created_at'], true) ? ($filters['sort'] ?? 'name') : 'name';
        $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return Role::query()->where('tenant_id', $tenantId)->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($q) => $q->whereLike('name', "%{$search}%")->orWhereLike('label', "%{$search}%")))->with('permissions')->orderBy($sort, $direction)->paginate($perPage);
    }

    public function find(string $tenantId, string $id): Role
    {
        return Role::query()->where('tenant_id', $tenantId)->with('permissions')->findOrFail($id);
    }
}
