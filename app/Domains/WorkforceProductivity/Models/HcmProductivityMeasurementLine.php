<?php

namespace App\Domains\WorkforceProductivity\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmProductivityMeasurementLine extends Model
{
    use HasUuids;

    protected $table = 'hcm_productivity_measurement_lines';

    protected $fillable = [
        'tenant_id',
        'measurement_id',
        'dimension_type',
        'dimension_id',
        'dimension_name',
        'output_contribution',
        'hours_contribution',
        'cost_contribution',
        'productivity_rate',
    ];

    protected $casts = [
        'output_contribution' => 'decimal:4',
        'hours_contribution' => 'decimal:2',
        'cost_contribution' => 'decimal:4',
        'productivity_rate' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function measurement(): BelongsTo
    {
        return $this->belongsTo(HcmProductivityMeasurement::class, 'measurement_id');
    }
}
