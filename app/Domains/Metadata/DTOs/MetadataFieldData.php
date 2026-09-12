<?php

namespace App\Domains\Metadata\DTOs;

final readonly class MetadataFieldData
{
    /** @param array<string, mixed>|null $configuration @param list<mixed>|null $validationRules @param array<string, mixed>|null $visibilityExpression @param array<string, mixed>|null $formula */
    public function __construct(public string $key, public string $label, public string $fieldType, public int $sortOrder, public ?array $configuration, public ?array $validationRules, public ?array $visibilityExpression, public ?array $formula) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self($data['key'], $data['label'], $data['field_type'], (int) ($data['sort_order'] ?? 0), $data['configuration'] ?? null, $data['validation_rules'] ?? null, $data['visibility_expression'] ?? null, $data['formula'] ?? null);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['key' => $this->key, 'label' => $this->label, 'field_type' => $this->fieldType, 'sort_order' => $this->sortOrder, 'configuration' => $this->configuration, 'validation_rules' => $this->validationRules, 'visibility_expression' => $this->visibilityExpression, 'formula' => $this->formula];
    }
}
