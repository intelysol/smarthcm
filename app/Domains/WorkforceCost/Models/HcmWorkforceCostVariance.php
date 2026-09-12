<?php

namespace App\Domains\WorkforceCost\Models;

use App\Domains\Organization\Models\CostCenter;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceCostVariance extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_cost_variances';

    protected $fillable = [
        'tenant_id',
        'department_id',
        'cost_center_id',
        'period_start',
        'period_end',
        'comparison_type',
        'planned_amount',
        'actual_amount',
        'variance_amount',
        'variance_percentage',
        'currency',
        'drivers',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'planned_amount' => 'decimal:4',
        'actual_amount' => 'decimal:4',
        'variance_amount' => 'decimal:4',
        'variance_percentage' => 'decimal:2',
        'drivers' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }
}