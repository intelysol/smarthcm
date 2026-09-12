<?php

namespace App\Domains\Employee\DTOs;

use App\Domains\Shared\DTOs\BaseData;

final readonly class EmployeeData extends BaseData
{
    public function __construct(
        public string $tenantId,
        public array $attributes,
        public int $actorId,
    ) {
    }

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

    public function toCreateArray(string $employeeNumber): array
    {
        return array_merge($this->toArray(), [
            'employee_number' => $employeeNumber,
            'created_by' => $this->actorId,
        ]);
    }
}
