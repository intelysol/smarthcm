<?php

namespace App\Domains\WorkforceCost\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmWorkforceCostModel extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_cost_models';

    protected $fillable = [
        'tenant_id',
        'name',
        'currency',
        'standard_labor_burden_rate',
        'standard_working_hours_per_fte_month',
        'default_allocation_method',
        'is_active',
    ];

    protected $casts = [
        'standard_labor_burden_rate' => 'decimal:2',
        'standard_working_hours_per_fte_month' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(HcmWorkforceCostSnapshot::class, 'model_id');
    }
}