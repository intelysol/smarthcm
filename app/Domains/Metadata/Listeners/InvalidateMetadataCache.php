<?php

namespace App\Domains\Metadata\Listeners;

use App\Domains\Metadata\Events\MetadataChanged;
use App\Domains\Metadata\Services\MetadataCacheService;

class InvalidateMetadataCache
{
    public function __construct(private readonly MetadataCacheService $cache) {}

    public function handle(MetadataChanged $event): void
    {
        $this->cache->invalidate($event->entity);
    }
}
