<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'succession_position_id', 'employee_id', 'priority',
    'readiness_timeframe', 'readiness_score', 'potential_rating', 'performance_rating',
    'risk_level', 'development_status', 'is_emergency_choice', 'override_readiness',
    'override_reason', 'version'
])]
class SuccessionCandidate extends CareerModel
{
    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'readiness_score' => 'decimal:2',
            'potential_rating' => 'decimal:2',
            'performance_rating' => 'decimal:2',
            'is_emergency_choice' => 'boolean',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<SuccessionPosition, SuccessionCandidate> */
    public function position(): BelongsTo
    {
        return $this->belongsTo(SuccessionPosition::class, 'succession_position_id');
    }

    /** @return BelongsTo<Employee, SuccessionCandidate> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return HasMany<SuccessionDevelopmentAction> */
    public function developmentActions(): HasMany
    {
        return $this->hasMany(SuccessionDevelopmentAction::class, 'candidate_id');
    }
}
