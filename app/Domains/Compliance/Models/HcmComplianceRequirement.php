<?php

namespace App\Domains\Compliance\Models;

use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Designation;
use App\Domains\Organization\Models\WorkLocation;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmComplianceRequirement extends Model
{
    use HasUuids;

    protected $table = 'hcm_compliance_requirements';

    protected $fillable = [
        'tenant_id',
        'requirement_type_id',
        'name',
        'code',
        'description',
        'country',
        'legal_entity_id',
        'branch_id',
        'location_id',
        'department_id',
        'job_id',
        'job_family',
        'position_id',
        'employee_type',
        'worker_type',
        'employment_type',
        'nationality_criteria',
        'effective_from',
        'effective_to',
        'is_mandatory',
        'renewal_required',
        'expiry_required',
        'grace_period_days',
        'verification_required',
        'document_required',
        'approval_required',
        'responsible_role',
        'escalation_policy',
        'warning_periods',
        'version',
        'is_active',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_mandatory' => 'boolean',
        'renewal_required' => 'boolean',
        'expiry_required' => 'boolean',
        'grace_period_days' => 'integer',
        'verification_required' => 'boolean',
        'document_required' => 'boolean',
        'approval_required' => 'boolean',
        'escalation_policy' => 'array',
        'warning_periods' => 'array',
        'version' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(HcmComplianceRequirementType::class, 'requirement_type_id');
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'legal_entity_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class, 'location_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'position_id');
    }

    public function employeeAssignments(): HasMany
    {
        return $this->hasMany(HcmEmployeeComplianceRequirement::class, 'requirement_id');
    }
}
