<?php

namespace App\Domains\WorkforceOptimization\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HcmWorkforceOptimizationModel extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_optimization_models';

    protected $fillable = [
        'tenant_id',
        'name',
        'model_type',
        'default_solver',
        'status',
        'current_version',
        'is_active',
        'description',
    ];

    protected $casts = [
        'current_version' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(HcmWorkforceOptimizationModelVersion::class, 'model_id');
    }

    public function currentVersion(): HasOne
    {
        return $this->hasOne(HcmWorkforceOptimizationModelVersion::class, 'model_id')
            ->where('is_current', true);
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(HcmWorkforceOptimizationObjective::class, 'model_id');
    }

    public function constraints(): HasMany
    {
        return $this->hasMany(HcmWorkforceOptimizationConstraint::class, 'model_id');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(HcmWorkforceOptimizationRun::class, 'model_id');
    }
}
