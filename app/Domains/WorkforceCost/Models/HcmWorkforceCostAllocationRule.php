<?php

namespace App\Domains\WorkforceCost\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmWorkforceCostAllocationRule extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_cost_allocation_rules';

    protected $fillable = [
        'tenant_id',
        'rule_name',
        'allocation_method',
        'source_type',
        'source_id',
        'targets',
        'priority',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected $casts = [
        'targets' => 'array',
        'priority' => 'integer',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(HcmWorkforceCostAllocation::class, 'allocation_rule_id');
    }
}