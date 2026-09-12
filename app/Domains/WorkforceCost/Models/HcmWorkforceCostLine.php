<?php

namespace App\Domains\WorkforceCost\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\CostCenter;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmWorkforceCostLine extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_cost_lines';

    protected $fillable = [
        'tenant_id',
        'snapshot_id',
        'employee_id',
        'department_id',
        'cost_center_id',
        'position_id',
        'job_id',
        'project_id',
        'worker_type',
        'cost_category',
        'cost_nature',
        'component_type',
        'source_domain',
        'source_record_id',
        'cost_date',
        'amount',
        'currency',
        'hours_worked',
        'rate_per_hour',
        'metadata',
    ];

    protected $casts = [
        'cost_date' => 'date',
        'amount' => 'decimal:4',
        'hours_worked' => 'decimal:2',
        'rate_per_hour' => 'decimal:4',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceCostSnapshot::class, 'snapshot_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(HcmWorkforceCostAllocation::class, 'cost_line_id');
    }
}