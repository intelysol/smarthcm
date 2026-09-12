<?php

namespace App\Domains\HealthSafety\Models;

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

class HcmHealthRequirement extends Model
{
    use HasUuids;

    protected $table = 'hcm_health_requirements';

    protected $fillable = [
        'tenant_id',
        'health_requirement_type_id',
        'code',
        'name',
        'description',
        'country',
        'company_id',
        'branch_id',
        'work_location_id',
        'department_id',
        'designation_id',
        'exposure_category',
        'is_mandatory',
        'is_safety_critical',
        'renewal_required',
        'validity_months',
        'grace_period_days',
        'reminder_days',
        'version',
        'is_active',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_safety_critical' => 'boolean',
        'renewal_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(HcmHealthRequirementType::class, 'health_requirement_type_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function workLocation(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function employeeRequirements(): HasMany
    {
        return $this->hasMany(HcmEmployeeHealthRequirement::class, 'health_requirement_id');
    }
}
