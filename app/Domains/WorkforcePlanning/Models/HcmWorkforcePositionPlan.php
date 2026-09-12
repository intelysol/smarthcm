<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\CostCenter;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Designation;
use App\Domains\Organization\Models\JobGrade;
use App\Domains\Organization\Models\Position;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class HcmWorkforcePositionPlan extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_workforce_position_plans';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'position_code',
        'title',
        'department_id',
        'branch_id',
        'job_grade_id',
        'designation_id',
        'cost_center_id',
        'status',
        'is_budgeted',
        'fte',
        'planned_start_date',
        'planned_end_date',
        'current_employee_id',
        'actual_position_id',
        'elimination_reason',
    ];

    protected $casts = [
        'is_budgeted' => 'boolean',
        'fte' => 'decimal:2',
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function currentEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'current_employee_id');
    }

    public function actualPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'actual_position_id');
    }

    public function budget(): HasOne
    {
        return $this->hasOne(HcmWorkforcePositionBudget::class, 'position_plan_id');
    }
}
