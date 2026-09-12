<?php

namespace App\Domains\Organization\Services;

use App\Domains\Organization\DTOs\OrganizationEntityData;
use App\Domains\Organization\Repositories\OrganizationEntityRepository;
use App\Domains\Organization\Support\OrganizationEntityDefinition;
use App\Domains\Shared\Services\ActivityLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class OrganizationEntityService
{
    public function __construct(
        private readonly OrganizationEntityRepository $records,
        private readonly ActivityLogService $activityLog,
    ) {
    }

    public function list(OrganizationEntityDefinition $definition, string $tenantId, array $filters): LengthAwarePaginator
    {
        return $this->records->paginate($definition, $tenantId, $filters);
    }

    public function find(OrganizationEntityDefinition $definition, string $tenantId, string $id): Model
    {
        return $this->records->findForTenant($definition, $tenantId, $id);
    }

    public function create(OrganizationEntityDefinition $definition, OrganizationEntityData $data): Model
    {
        $this->ensureUnique($definition, $data->tenantId, $data->attributes);

        $record = $this->records->create($definition, $data->toCreateArray());

        $this->activityLog->record('created', $record, $data->actorId, null, $record->attributesToArray());

        return $record;
    }

    public function update(OrganizationEntityDefinition $definition, Model $model, OrganizationEntityData $data): Model
    {
        $this->ensureUnique($definition, $data->tenantId, $data->attributes, (string) $model->getKey());

        $oldValues = $model->getOriginal();
        $record = $this->records->update($model, $data->toArray());

        $this->activityLog->record('updated', $record, $data->actorId, $oldValues, $record->attributesToArray());

        return $record;
    }

    public function delete(Model $model, int $actorId): bool
    {
        $oldValues = $model->attributesToArray();
        $deleted = $this->records->delete($model, $actorId);

        $this->activityLog->record('deleted', $model, $actorId, $oldValues, null);

        return $deleted;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function ensureUnique(OrganizationEntityDefinition $definition, string $tenantId, array $attributes, ?string $ignoreId = null): void
    {
        foreach ($definition->uniqueByTenant as $column) {
            if (! array_key_exists($column, $attributes)) {
                continue;
            }

            if ($this->records->existsForTenant($definition, $tenantId, $column, $attributes[$column], $ignoreId)) {
                throw ValidationException::withMessages([
                    $column => "The {$column} has already been used for this tenant.",
                ]);
            }
        }
    }
}
