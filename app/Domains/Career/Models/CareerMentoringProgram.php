<?php

namespace App\Domains\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'name', 'description', 'start_date', 'end_date', 'status', 'version'
])]
class CareerMentoringProgram extends CareerModel
{
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    /** @return HasMany<CareerMentoringRelationship> */
    public function relationships(): HasMany
    {
        return $this->hasMany(CareerMentoringRelationship::class, 'program_id');
    }
}
