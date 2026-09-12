<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceCapacityPlan extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_capacity_plans';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'capacity_metric_name',
        'workload_volume',
        'ratio_per_fte',
        'calculated_required_fte',
    ];

    protected $casts = [
        'workload_volume' => 'decimal:2',
        'ratio_per_fte' => 'decimal:2',
        'calculated_required_fte' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforcePlan::class, 'plan_id');
    }
}
