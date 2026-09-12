<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmAnalyticsKpiTarget extends Model
{
    use HasUuids;

    protected $table = 'hcm_analytics_kpi_targets';

    protected $fillable = [
        'tenant_id',
        'hcm_analytics_metric_id',
        'target_period',
        'target_value',
        'warning_threshold',
        'critical_threshold',
        'department_id',
        'branch_id',
    ];

    protected $casts = [
        'target_value' => 'decimal:4',
        'warning_threshold' => 'decimal:4',
        'critical_threshold' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function metric(): BelongsTo
    {
        return $this->belongsTo(HcmAnalyticsMetric::class, 'hcm_analytics_metric_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
