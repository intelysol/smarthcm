<?php

namespace App\Domains\WorkforceProductivity\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmProductivityScorecard extends Model
{
    use HasUuids;

    protected $table = 'hcm_productivity_scorecards';

    protected $fillable = [
        'tenant_id',
        'scorecard_level',
        'entity_id',
        'entity_name',
        'period_start',
        'period_end',
        'productivity_score',
        'utilization_rate',
        'quality_rate',
        'overtime_ratio',
        'cost_per_unit',
        'capacity_gap_pct',
        'status_label',
        'component_details',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'productivity_score' => 'decimal:2',
        'utilization_rate' => 'decimal:2',
        'quality_rate' => 'decimal:2',
        'overtime_ratio' => 'decimal:2',
        'cost_per_unit' => 'decimal:4',
        'capacity_gap_pct' => 'decimal:2',
        'component_details' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
