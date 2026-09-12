<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HcmWorkforcePlan extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_workforce_plans';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'planning_cycle',
        'planning_type',
        'start_date',
        'end_date',
        'status',
        'current_version',
        'currency',
        'company_id',
        'business_unit_id',
        'department_id',
        'owner_id',
        'locked_at',
        'locked_by',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'current_version' => 'integer',
        'locked_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(HcmWorkforcePlanVersion::class, 'plan_id');
    }

    public function periods(): HasMany
    {
        return $this->hasMany(HcmWorkforcePlanPeriod::class, 'plan_id')->orderBy('period_sequence', 'asc');
    }

    public function assumptions(): HasMany
    {
        return $this->hasMany(HcmWorkforcePlanAssumption::class, 'plan_id');
    }

    public function demandPlans(): HasMany
    {
        return $this->hasMany(HcmWorkforceDemandPlan::class, 'plan_id');
    }

    public function supplyPlans(): HasMany
    {
        return $this->hasMany(HcmWorkforceSupplyPlan::class, 'plan_id');
    }

    public function headcountPlans(): HasMany
    {
        return $this->hasMany(HcmWorkforceHeadcountPlan::class, 'plan_id');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(HcmWorkforcePositionPlan::class, 'plan_id');
    }

    public function hiringPlans(): HasMany
    {
        return $this->hasMany(HcmWorkforceHiringPlan::class, 'plan_id');
    }

    public function costPlans(): HasMany
    {
        return $this->hasMany(HcmWorkforceCostPlan::class, 'plan_id');
    }

    public function skillDemandPlans(): HasMany
    {
        return $this->hasMany(HcmWorkforceSkillDemandPlan::class, 'plan_id');
    }

    public function capacityPlans(): HasMany
    {
        return $this->hasMany(HcmWorkforceCapacityPlan::class, 'plan_id');
    }

    public function scenarios(): HasMany
    {
        return $this->hasMany(HcmWorkforceScenario::class, 'base_plan_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(HcmWorkforcePlanApproval::class, 'plan_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(HcmWorkforcePlanSnapshot::class, 'plan_id');
    }
}
