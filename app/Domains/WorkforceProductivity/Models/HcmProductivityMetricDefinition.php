<?php

namespace App\Domains\WorkforceProductivity\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HcmProductivityMetricDefinition extends Model
{
    use HasUuids;

    protected $table = 'hcm_productivity_metric_definitions';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'metric_type',
        'unit',
        'current_version',
        'status',
        'dimensions',
        'is_system',
    ];

    protected $casts = [
        'current_version' => 'integer',
        'dimensions' => 'array',
        'is_system' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(HcmProductivityMetricVersion::class, 'metric_definition_id');
    }

    public function currentVersion(): HasOne
    {
        return $this->hasOne(HcmProductivityMetricVersion::class, 'metric_definition_id')
            ->where('is_current', true);
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(HcmProductivityMeasurement::class, 'metric_definition_id');
    }
}
