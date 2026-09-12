<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Position;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforcePlanning\Models\HcmWorkforceHiringPlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePositionPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class HcmRecruitmentRequisition extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_recruitment_requisitions';

    protected $fillable = [
        'tenant_id',
        'requisition_number',
        'title',
        'position_id',
        'job_template_id',
        'department_id',
        'location_id',
        'hiring_manager_id',
        'recruiter_id',
        'employment_type',
        'openings',
        'priority',
        'reason',
        'min_salary',
        'max_salary',
        'budget_amount',
        'currency',
        'target_start_date',
        'description',
        'status',
        'is_confidential',
        'approved_at',
        'closed_at',
        'workforce_plan_id',
        'position_plan_id',
        'hiring_plan_id',
    ];

    protected $casts = [
        'openings' => 'integer',
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'budget_amount' => 'decimal:2',
        'target_start_date' => 'date',
        'is_confidential' => 'boolean',
        'approved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentJobTemplate::class, 'job_template_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function hiringManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'hiring_manager_id');
    }

    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    public function workforcePlan(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforcePlan::class, 'workforce_plan_id');
    }

    public function positionPlan(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforcePositionPlan::class, 'position_plan_id');
    }

    public function hiringPlan(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceHiringPlan::class, 'hiring_plan_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(HcmRecruitmentRequisitionApproval::class, 'requisition_id');
    }

    public function postings(): HasMany
    {
        return $this->hasMany(HcmRecruitmentJobPosting::class, 'requisition_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(HcmRecruitmentApplication::class, 'requisition_id');
    }
}
