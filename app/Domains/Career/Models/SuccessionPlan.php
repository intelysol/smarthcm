<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'name', 'description', 'scope_type', 'scope_id',
    'owner_id', 'review_date', 'status', 'version'
])]
class SuccessionPlan extends CareerModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'review_date' => 'date',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<Employee, SuccessionPlan> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'owner_id');
    }

    /** @return HasMany<SuccessionPosition> */
    public function positions(): HasMany
    {
        return $this->hasMany(SuccessionPosition::class, 'succession_plan_id');
    }

    /** @return HasMany<SuccessionScenario> */
    public function scenarios(): HasMany
    {
        return $this->hasMany(SuccessionScenario::class, 'succession_plan_id');
    }
}
