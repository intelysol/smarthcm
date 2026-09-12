<?php

namespace App\Domains\Career\Models;

use App\Domains\Organization\Models\Department;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'code', 'title', 'description', 'department_id',
    'job_family', 'status', 'version'
])]
class CareerPath extends CareerModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<Department, CareerPath> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /** @return HasMany<CareerPathStep> */
    public function steps(): HasMany
    {
        return $this->hasMany(CareerPathStep::class, 'career_path_id')->orderBy('sequence');
    }
}
