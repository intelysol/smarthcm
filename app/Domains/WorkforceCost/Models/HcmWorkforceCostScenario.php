<?php

namespace App\Domains\WorkforceCost\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceCostScenario extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_cost_scenarios';

    protected $fillable = [
        'tenant_id',
        'base_scenario_id',
        'name',
        'scenario_type',
        'current_cost',
        'scenario_cost',
        'cost_difference',
        'headcount_difference',
        'fte_difference',
        'capacity_difference_hours',
        'estimated_roi_percentage',
        'payback_months',
        'status',
        'currency',
        'parameters',
        'created_by',
    ];

    protected $casts = [
        'current_cost' => 'decimal:4',
        'scenario_cost' => 'decimal:4',
        'cost_difference' => 'decimal:4',
        'headcount_difference' => 'decimal:2',
        'fte_difference' => 'decimal:2',
        'capacity_difference_hours' => 'decimal:2',
        'estimated_roi_percentage' => 'decimal:2',
        'payback_months' => 'decimal:1',
        'parameters' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}