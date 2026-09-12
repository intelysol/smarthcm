<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\JobGrade;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceHiringPlan extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_hiring_plans';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'position_plan_id',
        'title',
        'department_id',
        'job_grade_id',
        'planned_start_date',
        'priority',
        'reason',
        'replacement_type',
        'replaces_employee_id',
        'status',
        'job_requisition_id',
        'hiring_manager_id',
    ];

    protected $casts = [
        'planned_start_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforcePlan::class, 'plan_id');
    }

    public function positionPlan(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforcePositionPlan::class, 'position_plan_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class);
    }

    public function replacesEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'replaces_employee_id');
    }

    public function hiringManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hiring_manager_id');
    }
}
