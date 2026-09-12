<?php

namespace App\Domains\WorkforceOptimization\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceOptimizationModelVersion extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_optimization_model_versions';

    protected $fillable = [
        'tenant_id',
        'model_id',
        'version',
        'configuration',
        'effective_from',
        'effective_to',
        'is_current',
        'change_notes',
    ];

    protected $casts = [
        'version' => 'integer',
        'configuration' => 'array',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_current' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceOptimizationModel::class, 'model_id');
    }

    public function objectives(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HcmWorkforceOptimizationObjective::class, 'model_id', 'model_id');
    }
}
