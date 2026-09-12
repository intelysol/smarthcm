<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'title', 'scope_type', 'scope_id', 'review_date',
    'status', 'workflow_instance_id', 'finalized_by', 'finalized_at', 'version'
])]
class TalentReviewSession extends CareerModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'review_date' => 'date',
            'finalized_at' => 'datetime',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<Employee, TalentReviewSession> */
    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'finalized_by');
    }

    /** @return HasMany<TalentReviewRecord> */
    public function records(): HasMany
    {
        return $this->hasMany(TalentReviewRecord::class, 'session_id');
    }
}
