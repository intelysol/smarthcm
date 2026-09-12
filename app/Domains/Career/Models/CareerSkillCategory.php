<?php

namespace App\Domains\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'code', 'name', 'description', 'status', 'sort_order', 'version'
])]
class CareerSkillCategory extends CareerModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    /** @return HasMany<CareerSkill> */
    public function skills(): HasMany
    {
        return $this->hasMany(CareerSkill::class, 'category_id');
    }
}
