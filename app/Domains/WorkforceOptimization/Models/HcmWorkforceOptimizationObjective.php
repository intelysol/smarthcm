<?php

namespace App\Domains\WorkforceOptimization\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceOptimizationObjective extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_optimization_objectives';

    protected $fillable = [
        'tenant_id',
        'model_id',
        'code',
        'name',
        'direction',
        'target_metric',
        'weight',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceOptimizationModel::class, 'model_id');
    }
}
