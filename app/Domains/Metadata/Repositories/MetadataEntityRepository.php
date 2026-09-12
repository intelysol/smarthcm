<?php

namespace App\Domains\Metadata\Repositories;

use App\Domains\Metadata\Models\MetadataEntity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MetadataEntityRepository
{
    /** @param array<string, mixed> $filters */
    public function paginate(string $tenantId, array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 25), 1), 100);
        $sort = in_array($filters['sort'] ?? 'key', ['key', 'label', 'status', 'version', 'updated_at'], true) ? ($filters['sort'] ?? 'key') : 'key';
        $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return MetadataEntity::query()->where('tenant_id', $tenantId)->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($q) => $q->whereLike('key', "%{$search}%")->orWhereLike('label', "%{$search}%")))->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))->withCount('fields')->orderBy($sort, $direction)->paginate($perPage);
    }

    public function find(string $tenantId, string $id): MetadataEntity
    {
        return MetadataEntity::query()->where('tenant_id', $tenantId)->with(['fields', 'forms'])->findOrFail($id);
    }
}
