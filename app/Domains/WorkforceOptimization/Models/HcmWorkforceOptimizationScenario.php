<?php

namespace App\Domains\WorkforceOptimization\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmWorkforceOptimizationScenario extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_optimization_scenarios';

    protected $fillable = [
        'tenant_id',
        'opportunity_id',
        'scenario_name',
        'scenario_type',
        'baseline_snapshot',
        'status',
    ];

    protected $casts = [
        'baseline_snapshot' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceOptimizationOpportunity::class, 'opportunity_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(HcmWorkforceOptimizationScenarioResult::class, 'scenario_id');
    }
}
