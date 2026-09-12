<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Mobility\Enums\AssignmentStatus;
use App\Domains\Mobility\Enums\MobilityType;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Job;
use App\Domains\Organization\Models\Position;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MobilityAssignment extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_mobility_assignments';

    protected $fillable = [
        'tenant_id',
        'mobility_request_id',
        'employee_id',
        'program_id',
        'assignment_number',
        'mobility_type',
        'status',
        'current_version',
        'home_country',
        'host_country',
        'home_company_id',
        'home_branch_id',
        'home_department_id',
        'home_position_id',
        'home_job_id',
        'home_manager_id',
        'host_company_id',
        'host_branch_id',
        'host_department_id',
        'host_position_id',
        'host_job_id',
        'host_manager_id',
        'start_date',
        'planned_end_date',
        'actual_end_date',
        'home_currency',
        'host_currency',
        'assignment_currency',
        'assignment_sponsor_id',
        'mobility_owner_id',
        'purpose',
        'project_code',
        'cost_center_code',
        'is_repatriated',
        'repatriation_date',
        'notes',
    ];

    protected $casts = [
        'mobility_type' => MobilityType::class,
        'status' => AssignmentStatus::class,
        'current_version' => 'integer',
        'start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_end_date' => 'date',
        'is_repatriated' => 'boolean',
        'repatriation_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(MobilityRequest::class, 'mobility_request_id');
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

    public function versions(): HasMany
    {
        return $this->hasMany(MobilityAssignmentVersion::class, 'assignment_id');
    }

    public function terms(): HasOne
    {
        return $this->hasOne(MobilityAssignmentTerm::class, 'assignment_id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(MobilityAssignmentLocation::class, 'assignment_id');
    }

    public function costs(): HasMany
    {
        return $this->hasMany(MobilityAssignmentCost::class, 'assignment_id');
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(MobilityAssignmentBudget::class, 'assignment_id');
    }

    public function costAllocations(): HasMany
    {
        return $this->hasMany(MobilityCostAllocation::class, 'assignment_id');
    }

    public function relocationCase(): HasOne
    {
        return $this->hasOne(MobilityRelocationCase::class, 'assignment_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(MobilityTask::class, 'assignment_id');
    }

    public function complianceLinks(): HasMany
    {
        return $this->hasMany(MobilityComplianceLink::class, 'assignment_id');
    }

    public function documentLinks(): HasMany
    {
        return $this->hasMany(MobilityDocumentLink::class, 'assignment_id');
    }

    public function expenseLinks(): HasMany
    {
        return $this->hasMany(MobilityExpenseLink::class, 'assignment_id');
    }

    public function benefitLinks(): HasMany
    {
        return $this->hasMany(MobilityBenefitLink::class, 'assignment_id');
    }

    public function compensationLinks(): HasMany
    {
        return $this->hasMany(MobilityCompensationLink::class, 'assignment_id');
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(MobilityExtension::class, 'assignment_id');
    }

    public function changes(): HasMany
    {
        return $this->hasMany(MobilityChange::class, 'assignment_id');
    }

    public function repatriation(): HasOne
    {
        return $this->hasOne(MobilityRepatriation::class, 'assignment_id');
    }
}
