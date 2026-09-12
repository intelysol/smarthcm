<?php

namespace App\Domains\WorkforceCost\Models;

use App\Domains\Organization\Models\CostCenter;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceCostAllocation extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_cost_allocations';

    protected $fillable = [
        'tenant_id',
        'cost_line_id',
        'allocation_rule_id',
        'target_department_id',
        'target_cost_center_id',
        'target_project_id',
        'allocated_percentage',
        'allocated_amount',
        'currency',
        'status',
    ];

    protected $casts = [
        'allocated_percentage' => 'decimal:2',
        'allocated_amount' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function costLine(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceCostLine::class, 'cost_line_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceCostAllocationRule::class, 'allocation_rule_id');
    }

    public function targetDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'target_department_id');
    }

    public function targetCostCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'target_cost_center_id');
    }
}