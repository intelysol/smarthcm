<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Mobility\Enums\EligibilityStatus;
use App\Domains\Mobility\Enums\MobilityRequestStatus;
use App\Domains\Mobility\Enums\MobilityType;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Job;
use App\Domains\Organization\Models\Position;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MobilityRequest extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_mobility_requests';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'program_id',
        'request_number',
        'mobility_type',
        'status',
        'home_company_id',
        'home_country',
        'home_branch_id',
        'home_department_id',
        'home_position_id',
        'home_job_id',
        'home_manager_id',
        'host_company_id',
        'host_country',
        'host_branch_id',
        'host_department_id',
        'host_position_id',
        'host_job_id',
        'host_manager_id',
        'proposed_start_date',
        'proposed_end_date',
        'duration_months',
        'business_justification',
        'assignment_reason',
        'project_id',
        'cost_center_code',
        'assignment_sponsor_id',
        'mobility_owner_id',
        'eligibility_status',
        'eligibility_details',
        'workflow_instance_id',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'mobility_type' => MobilityType::class,
        'status' => MobilityRequestStatus::class,
        'eligibility_status' => EligibilityStatus::class,
        'proposed_start_date' => 'date',
        'proposed_end_date' => 'date',
        'duration_months' => 'integer',
        'eligibility_details' => 'array',
        'approved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(MobilityProgram::class, 'program_id');
    }

    public function homeCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'home_company_id');
    }

    public function homeBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'home_branch_id');
    }

    public function homeDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'home_department_id');
    }

    public function homePosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'home_position_id');
    }

    public function homeJob(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'home_job_id');
    }

    public function homeManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'home_manager_id');
    }

    public function hostCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'host_company_id');
    }

    public function hostBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'host_branch_id');
    }

    public function hostDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'host_department_id');
    }

    public function hostPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'host_position_id');
    }

    public function hostJob(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'host_job_id');
    }

    public function hostManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'host_manager_id');
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(MobilityAssignment::class, 'mobility_request_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
