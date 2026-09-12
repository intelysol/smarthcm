<?php

namespace App\Domains\Metadata\Actions;

use App\Domains\Metadata\DTOs\MetadataEntityData;
use App\Domains\Metadata\Models\MetadataEntity;
use App\Domains\Metadata\Services\MetadataService;
use App\Models\User;

class CreateMetadataEntityAction
{
    public function __construct(private readonly MetadataService $metadata) {}

    public function execute(string $tenantId, User $actor, MetadataEntityData $data): MetadataEntity
    {
        return $this->metadata->createEntity($tenantId, $actor, $data);
    }
}
