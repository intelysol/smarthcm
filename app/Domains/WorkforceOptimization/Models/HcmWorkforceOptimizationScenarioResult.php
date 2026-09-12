<?php

namespace App\Domains\WorkforceOptimization\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceOptimizationScenarioResult extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_optimization_scenario_results';

    protected $fillable = [
        'tenant_id',
        'scenario_id',
        'option_label',
        'action_type',
        'total_cost',
        'cost_delta',
        'capacity_gained_hours',
        'productivity_impact_pct',
        'time_to_capacity_days',
        'payback_period_months',
        'projected_roi_pct',
        'is_recommended',
        'pareto_rank',
        'details',
    ];

    protected $casts = [
        'total_cost' => 'decimal:4',
        'cost_delta' => 'decimal:4',
        'capacity_gained_hours' => 'decimal:2',
        'productivity_impact_pct' => 'decimal:2',
        'time_to_capacity_days' => 'integer',
        'payback_period_months' => 'decimal:2',
        'projected_roi_pct' => 'decimal:2',
        'is_recommended' => 'boolean',
        'pareto_rank' => 'integer',
        'details' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceOptimizationScenario::class, 'scenario_id');
    }
}
