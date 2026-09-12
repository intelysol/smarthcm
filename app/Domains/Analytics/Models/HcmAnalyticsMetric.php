<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HcmAnalyticsMetric extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_analytics_metrics';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'category',
        'description',
        'formula',
        'unit',
        'aggregation',
        'sensitivity',
        'refresh_frequency',
        'current_version',
        'owner',
        'data_sources',
        'is_active',
    ];

    protected $casts = [
        'data_sources' => 'array',
        'current_version' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(HcmAnalyticsMetricVersion::class, 'hcm_analytics_metric_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(HcmAnalyticsKpiTarget::class, 'hcm_analytics_metric_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(HcmAnalyticsAlert::class, 'hcm_analytics_metric_id');
    }
}
