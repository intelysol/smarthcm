<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'code', 'name', 'description', 'criteria', 'owner_id',
    'status', 'version'
])]
class TalentPool extends CareerModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'criteria' => 'array',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<Employee, TalentPool> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'owner_id');
    }

    /** @return HasMany<TalentPoolMember> */
    public function members(): HasMany
    {
        return $this->hasMany(TalentPoolMember::class, 'pool_id');
    }
}
