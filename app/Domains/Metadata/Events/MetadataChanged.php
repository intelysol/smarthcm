<?php

namespace App\Domains\Metadata\Events;

use App\Domains\Metadata\Models\MetadataEntity;
use Illuminate\Foundation\Events\Dispatchable;

class MetadataChanged
{
    use Dispatchable;

    public function __construct(public readonly MetadataEntity $entity, public readonly string $action) {}
}
