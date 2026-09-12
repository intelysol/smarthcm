<?php

namespace App\Domains\Shared\DTOs;

abstract readonly class BaseData
{
    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
