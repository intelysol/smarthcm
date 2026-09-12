<?php

namespace App\Domains\WorkforceProductivity\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmProductivityMetricVersion extends Model
{
    use HasUuids;

    protected $table = 'hcm_productivity_metric_versions';

    protected $fillable = [
        'tenant_id',
        'metric_definition_id',
        'version',
        'formula_name',
        'numerator_code',
        'denominator_code',
        'calculation_expression',
        'effective_from',
        'effective_to',
        'is_current',
        'change_notes',
    ];

    protected $casts = [
        'version' => 'integer',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_current' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(HcmProductivityMetricDefinition::class, 'metric_definition_id');
    }
}
