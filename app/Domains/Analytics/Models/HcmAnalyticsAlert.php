<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmAnalyticsAlert extends Model
{
    use HasUuids;

    protected $table = 'hcm_analytics_alerts';

    protected $fillable = [
        'tenant_id',
        'hcm_analytics_metric_id',
        'title',
        'comparison_operator',
        'threshold_value',
        'severity',
        'filters',
        'is_active',
    ];

    protected $casts = [
        'threshold_value' => 'decimal:4',
        'filters' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function metric(): BelongsTo
    {
        return $this->belongsTo(HcmAnalyticsMetric::class, 'hcm_analytics_metric_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(HcmAnalyticsAlertSubscription::class, 'hcm_analytics_alert_id');
    }
}
