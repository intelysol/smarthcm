<?php

namespace App\Domains\Rules\Repositories;

use App\Domains\Rules\Models\BusinessRule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BusinessRuleRepository
{
    /** @param array<string, mixed> $filters */
    public function paginate(string $tenantId, array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 25), 1), 100);
        return BusinessRule::query()->where('tenant_id', $tenantId)->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))->when($filters['category'] ?? null, fn ($query, $value) => $query->where('category', $value))->when($filters['search'] ?? null, fn ($query, $value) => $query->where(fn ($q) => $q->whereLike('key', "%{$value}%")->orWhereLike('name', "%{$value}%")))->orderBy('priority')->paginate($perPage);
    }
    public function find(string $tenantId, string $id): BusinessRule { return BusinessRule::query()->where('tenant_id', $tenantId)->findOrFail($id); }
}
