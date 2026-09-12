<?php

namespace App\Domains\Metadata\DTOs;

final readonly class MetadataEntityData
{
    /** @param array<string, mixed>|null $settings */
    public function __construct(public string $key, public string $label, public ?string $module, public string $entityType, public ?string $category, public ?string $icon, public ?string $color, public ?string $description, public string $status, public ?array $settings, public bool $supportsSoftDeletes, public bool $supportsAudit) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self($data['key'], $data['label'], $data['module'] ?? null, $data['entity_type'], $data['category'] ?? null, $data['icon'] ?? null, $data['color'] ?? null, $data['description'] ?? null, $data['status'] ?? 'draft', $data['settings'] ?? null, $data['supports_soft_deletes'] ?? true, $data['supports_audit'] ?? true);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['key' => $this->key, 'label' => $this->label, 'module' => $this->module, 'entity_type' => $this->entityType, 'category' => $this->category, 'icon' => $this->icon, 'color' => $this->color, 'description' => $this->description, 'status' => $this->status, 'settings' => $this->settings, 'supports_soft_deletes' => $this->supportsSoftDeletes, 'supports_audit' => $this->supportsAudit];
    }
}
