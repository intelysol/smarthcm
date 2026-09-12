<?php

namespace App\Domains\Metadata\Services;

use App\Domains\Metadata\Models\MetadataEntity;
use Illuminate\Support\Facades\Cache;

class MetadataCacheService
{
    /** @return array<string, mixed> */
    public function definition(MetadataEntity $entity): array
    {
        $key = $this->key($entity);

        return Cache::remember($key, now()->addHour(), fn () => ['entity' => $entity->loadMissing(['fields', 'forms'])->toArray(), 'version' => $entity->version]);
    }

    public function invalidate(MetadataEntity $entity): void
    {
        Cache::forget($this->key($entity));
    }

    private function key(MetadataEntity $entity): string
    {
        return "metadata:{$entity->tenant_id}:{$entity->id}:{$entity->version}";
    }
}
