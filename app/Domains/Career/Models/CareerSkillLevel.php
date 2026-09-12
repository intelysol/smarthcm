<?php

namespace App\Domains\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'level_number', 'name', 'description', 'behavioral_indicators', 'version'
])]
class CareerSkillLevel extends CareerModel
{
    protected function casts(): array
    {
        return [
            'level_number' => 'integer',
            'behavioral_indicators' => 'array',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }
}
