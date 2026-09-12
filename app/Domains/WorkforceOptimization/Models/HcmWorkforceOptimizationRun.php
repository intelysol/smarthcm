<?php

namespace App\Domains\WorkforceOptimization\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmWorkforceOptimizationRun extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_optimization_runs';

    protected $fillable = [
        'tenant_id',
        'run_number',
        'model_id',
        'model_version_id',
        'scope_type',
        'scope_id',
        'solver_type',
        'input_snapshot',
        'opportunities_count',
        'recommendations_count',
        'execution_duration_ms',
        'status',
        'error_message',
        'initiated_by',
    ];

    protected $casts = [
        'input_snapshot' => 'array',
        'opportunities_count' => 'integer',
        'recommendations_count' => 'integer',
        'execution_duration_ms' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceOptimizationModel::class, 'model_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceOptimizationModelVersion::class, 'model_version_id');
    }

    public function modelVersion(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceOptimizationModelVersion::class, 'model_version_id');
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(HcmWorkforceOptimizationOpportunity::class, 'run_id');
    }

    public function recommendations(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            HcmWorkforceOptimizationRecommendation::class,
            HcmWorkforceOptimizationOpportunity::class,
            'run_id',
            'opportunity_id',
            'id',
            'id'
        );
    }

    public function scenarios(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            HcmWorkforceOptimizationScenario::class,
            HcmWorkforceOptimizationOpportunity::class,
            'run_id',
            'opportunity_id',
            'id',
            'id'
        );
    }
}
