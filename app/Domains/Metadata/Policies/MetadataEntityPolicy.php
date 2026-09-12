<?php

namespace App\Domains\Metadata\Policies;

use App\Domains\Metadata\Models\MetadataEntity;
use App\Models\User;

class MetadataEntityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('metadata.entities.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('metadata.entities.manage');
    }

    public function update(User $user, MetadataEntity $entity): bool
    {
        return (string) $user->tenant_id === (string) $entity->tenant_id && $user->hasPermission('metadata.entities.manage');
    }

    public function delete(User $user, MetadataEntity $entity): bool
    {
        return $this->update($user, $entity);
    }
}
