<?php

namespace App\Domains\Organization\DTOs;

use App\Domains\Shared\DTOs\BaseData;

final readonly class OrganizationEntityData extends BaseData
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public string $tenantId,
        public array $attributes,
        public int $actorId,
    ) {
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function fromArray(array $attributes, string $tenantId, int $actorId): self
    {
        return new self($tenantId, $attributes, $actorId);
    }

    public function toArray(): array
    {
        return array_merge($this->attributes, [
            'tenant_id' => $this->tenantId,
            'updated_by' => $this->actorId,
        ]);
    }

    public function toCreateArray(): array
    {
        return array_merge($this->toArray(), [
            'created_by' => $this->actorId,
        ]);
    }
}
