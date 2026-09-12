<?php

namespace App\Domains\WorkforceOptimization\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceOptimizationConstraint extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_optimization_constraints';

    protected $fillable = [
        'tenant_id',
        'model_id',
        'constraint_type',
        'name',
        'is_hard_constraint',
        'parameters',
        'is_active',
    ];

    protected $casts = [
        'is_hard_constraint' => 'boolean',
        'parameters' => 'array',
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
