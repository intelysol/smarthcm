<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmAnalyticsDashboardWidget extends Model
{
    use HasUuids;

    protected $table = 'hcm_analytics_dashboard_widgets';

    protected $fillable = [
        'tenant_id',
        'dashboard_id',
        'metric_id',
        'widget_type',
        'title',
        'position_x',
        'position_y',
        'width',
        'height',
        'query_config',
    ];

    protected $casts = [
        'position_x' => 'integer',
        'position_y' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'query_config' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(HcmAnalyticsDashboardDefinition::class, 'dashboard_id');
    }

    public function metric(): BelongsTo
    {
        return $this->belongsTo(HcmAnalyticsMetric::class, 'metric_id');
    }
}
