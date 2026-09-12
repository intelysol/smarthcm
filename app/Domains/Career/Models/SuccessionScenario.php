<?php

namespace App\Domains\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'succession_plan_id', 'name', 'trigger_event',
    'description', 'status', 'version'
])]
class SuccessionScenario extends CareerModel
{
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

    /** @return BelongsTo<SuccessionPlan, SuccessionScenario> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SuccessionPlan::class, 'succession_plan_id');
    }

    /** @return HasMany<SuccessionScenarioCandidate> */
    public function scenarioCandidates(): HasMany
    {
        return $this->hasMany(SuccessionScenarioCandidate::class, 'scenario_id');
    }
}
