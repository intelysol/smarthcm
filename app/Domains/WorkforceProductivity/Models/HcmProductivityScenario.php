<?php

namespace App\Domains\WorkforceProductivity\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmProductivityScenario extends Model
{
    use HasUuids;

    protected $table = 'hcm_productivity_scenarios';

    protected $fillable = [
        'tenant_id',
        'scenario_name',
        'scenario_type',
        'baseline_snapshot_id',
        'headcount_delta',
        'fte_delta',
        'capacity_hours_delta',
        'expected_output_delta',
        'cost_delta',
        'projected_cost_per_unit',
        'projected_roi_pct',
        'assumptions',
    ];

    protected $casts = [
        'headcount_delta' => 'integer',
        'fte_delta' => 'decimal:2',
        'capacity_hours_delta' => 'decimal:2',
        'expected_output_delta' => 'decimal:4',
        'cost_delta' => 'decimal:4',
        'projected_cost_per_unit' => 'decimal:4',
        'projected_roi_pct' => 'decimal:2',
        'assumptions' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function baselineSnapshot(): BelongsTo
    {
        return $this->belongsTo(HcmProductivitySnapshot::class, 'baseline_snapshot_id');
    }
}
