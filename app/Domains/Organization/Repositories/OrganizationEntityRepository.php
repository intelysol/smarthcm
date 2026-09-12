<?php

namespace App\Domains\Organization\Repositories;

use App\Domains\Organization\Support\OrganizationEntityDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrganizationEntityRepository
{
    public function paginate(OrganizationEntityDefinition $definition, string $tenantId, array $filters): LengthAwarePaginator
    {
        $query = $this->scopedQuery($definition, $tenantId);

        if (($filters['search'] ?? null) !== null) {
            $search = (string) $filters['search'];
            $query->where(function (Builder $builder) use ($definition, $search): void {
                foreach ($definition->searchable as $column) {
                    $builder->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        foreach ($definition->filterable as $column) {
            if (array_key_exists($column, $filters) && $filters[$column] !== null && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }

        return $query->latest()->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findForTenant(OrganizationEntityDefinition $definition, string $tenantId, string $id): Model
    {
        return $this->scopedQuery($definition, $tenantId)->findOrFail($id);
    }

    public function create(OrganizationEntityDefinition $definition, array $attributes): Model
    {
        return $definition->modelClass::query()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes);
        $model->save();

        return $model->refresh();
    }

    public function existsForTenant(OrganizationEntityDefinition $definition, string $tenantId, string $column, mixed $value, ?string $ignoreId = null): bool
    {
        $query = $this->scopedQuery($definition, $tenantId)->where($column, $value);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }

    public function delete(Model $model, int $actorId): bool
    {
        $model->forceFill(['deleted_by' => $actorId])->save();

        return (bool) $model->delete();
    }

    public function allForExport(OrganizationEntityDefinition $definition, string $tenantId, array $filters): iterable
    {
        return $this->paginate($definition, $tenantId, array_merge($filters, ['per_page' => 1000]))->items();
    }

    private function scopedQuery(OrganizationEntityDefinition $definition, string $tenantId): Builder
    {
        return $definition->modelClass::query()->where('tenant_id', $tenantId);
    }
}
