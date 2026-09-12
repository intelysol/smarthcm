<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmAnalyticsMetricVersion extends Model
{
    use HasUuids;

    protected $table = 'hcm_analytics_metric_versions';

    protected $fillable = [
        'tenant_id',
        'hcm_analytics_metric_id',
        'version_number',
        'effective_from',
        'effective_to',
        'calculation_definition',
        'change_summary',
        'created_by',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'calculation_definition' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function metric(): BelongsTo
    {
        return $this->belongsTo(HcmAnalyticsMetric::class, 'hcm_analytics_metric_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
