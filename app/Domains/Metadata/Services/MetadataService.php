<?php

namespace App\Domains\Metadata\Services;

use App\Domains\Metadata\DTOs\MetadataEntityData;
use App\Domains\Metadata\DTOs\MetadataFieldData;
use App\Domains\Metadata\Events\MetadataChanged;
use App\Domains\Metadata\Models\MetadataArtifact;
use App\Domains\Metadata\Models\MetadataAudit;
use App\Domains\Metadata\Models\MetadataEntity;
use App\Domains\Metadata\Models\MetadataField;
use App\Domains\Metadata\Models\MetadataRelationship;
use App\Domains\Shared\Services\ActivityLogService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MetadataService
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function createEntity(string $tenantId, User $actor, MetadataEntityData $data): MetadataEntity
    {
        return DB::transaction(function () use ($tenantId, $actor, $data): MetadataEntity {
            $entity = MetadataEntity::query()->create([...$data->toArray(), 'tenant_id' => $tenantId, 'version' => 1, 'row_version' => 1, 'created_by' => $actor->id, 'updated_by' => $actor->id]);
            $this->audit('entity.created', $entity, $actor, null, $entity->attributesToArray());
            MetadataChanged::dispatch($entity, 'entity.created');

            return $entity;
        });
    }

    public function addField(MetadataEntity $entity, User $actor, MetadataFieldData $data): MetadataField
    {
        $field = $entity->fields()->create([...$data->toArray(), 'row_version' => 1, 'created_by' => $actor->id, 'updated_by' => $actor->id]);
        $this->touchEntity($entity, $actor, 'field.created', null, $field->attributesToArray());

        return $field;
    }

    /** @param array<string, mixed> $data */
    public function createRelationship(MetadataEntity $source, MetadataEntity $target, User $actor, array $data): MetadataRelationship
    {
        if ((string) $source->tenant_id !== (string) $target->tenant_id) {
            throw ValidationException::withMessages(['target_entity_id' => 'Relationships cannot cross tenant boundaries.']);
        }
        $relationship = MetadataRelationship::query()->create([...$data, 'source_entity_id' => $source->id, 'target_entity_id' => $target->id, 'created_by' => $actor->id]);
        $this->touchEntity($source, $actor, 'relationship.created', null, $relationship->attributesToArray());

        return $relationship;
    }

    /** @param array<string, mixed> $data */
    public function createArtifact(string $tenantId, User $actor, array $data): MetadataArtifact
    {
        $artifact = MetadataArtifact::query()->create([...$data, 'tenant_id' => $tenantId, 'version' => 1, 'created_by' => $actor->id, 'updated_by' => $actor->id]);
        $this->audit('artifact.created', $artifact, $actor, null, $artifact->attributesToArray());

        return $artifact;
    }

    public function publishArtifact(MetadataArtifact $artifact, User $actor): MetadataArtifact
    {
        if (! in_array($artifact->status, ['draft', 'approved'], true)) {
            throw ValidationException::withMessages(['status' => 'Only draft or approved metadata can be published.']);
        }
        $artifact->update(['status' => 'published', 'reviewed_by' => $actor->id, 'approved_at' => now(), 'updated_by' => $actor->id]);
        MetadataChanged::dispatch($artifact->fresh(), 'artifact.published');
        return $artifact->fresh();
    }

    public function deprecateArtifact(MetadataArtifact $artifact, User $actor): MetadataArtifact
    {
        $artifact->update(['status' => 'deprecated', 'updated_by' => $actor->id]);
        MetadataChanged::dispatch($artifact->fresh(), 'artifact.deprecated');
        return $artifact->fresh();
    }

    /** @param array<string, mixed> $data */
    public function createRecord(MetadataEntity $entity, User $actor, array $data): array
    {
        $this->validateRecord($entity, $data);
        $record = $entity->records()->create(['tenant_id' => $entity->tenant_id, 'data' => $data, 'status' => 'active', 'created_by' => $actor->id, 'updated_by' => $actor->id, 'row_version' => 1]);
        $this->audit('record.created', $record, $actor, null, $record->attributesToArray());

        return $record->toArray();
    }

    /** @param array<string, mixed> $data */
    private function validateRecord(MetadataEntity $entity, array $data): void
    {
        $errors = [];
        foreach ($entity->fields as $field) {
            $configuration = $field->configuration ?? [];
            $value = $data[$field->key] ?? null;
            if (($configuration['required'] ?? false) && ($value === null || $value === '')) {
                $errors[$field->key][] = 'This field is required.';
            }
            if ($value !== null && isset($configuration['max_length']) && is_string($value) && mb_strlen($value) > $configuration['max_length']) {
                $errors[$field->key][] = 'The value exceeds the maximum length.';
            }
            foreach ($field->validation_rules ?? [] as $rule) {
                if (($rule['type'] ?? null) === 'regex' && $value !== null && ! preg_match($rule['pattern'], (string) $value)) {
                    $errors[$field->key][] = $rule['message'] ?? 'The value format is invalid.';
                }
            }
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /** @param array<string, mixed>|null $old @param array<string, mixed>|null $new */
    private function touchEntity(MetadataEntity $entity, User $actor, string $action, ?array $old, ?array $new): void
    {
        $entity->increment('version');
        $entity->increment('row_version');
        $entity->forceFill(['updated_by' => $actor->id])->save();
        $this->audit($action, $entity, $actor, $old, $new);
        MetadataChanged::dispatch($entity->fresh(), $action);
    }

    /** @param array<string, mixed>|null $old @param array<string, mixed>|null $new */
    private function audit(string $action, object $model, User $actor, ?array $old, ?array $new): void
    {
        $tenantId = (string) $model->getAttribute('tenant_id');
        MetadataAudit::query()->create(['tenant_id' => $tenantId, 'subject_type' => $model::class, 'subject_id' => (string) $model->getKey(), 'user_id' => $actor->id, 'action' => $action, 'old_values' => $old, 'new_values' => $new, 'occurred_at' => now()]);
        $this->activityLog->record($action, $model, $actor->id, $old, $new);
    }
}
