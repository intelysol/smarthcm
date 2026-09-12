<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\JobGrade;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceDemandPlan extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_demand_plans';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'department_id',
        'job_grade_id',
        'job_family',
        'driver_name',
        'current_fte',
        'required_fte',
        'demand_gap_fte',
        'driver_inputs',
        'justification',
    ];

    protected $casts = [
        'current_fte' => 'decimal:2',
        'required_fte' => 'decimal:2',
        'demand_gap_fte' => 'decimal:2',
        'driver_inputs' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforcePlan::class, 'plan_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class);
    }
}
