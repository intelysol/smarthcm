<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RetirementPlan extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'retirement_plans';

    protected $fillable = [
        'tenant_id',
        'plan_code',
        'name',
        'plan_type',
        'currency',
        'provider',
        'country',
        'default_employee_rate',
        'default_employer_match_rate',
        'max_employer_contribution_rate',
        'contribution_base',
        'vesting_type',
        'cliff_months',
        'is_mandatory',
        'is_active',
        'version',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'default_employee_rate' => 'decimal:4',
        'default_employer_match_rate' => 'decimal:4',
        'max_employer_contribution_rate' => 'decimal:4',
        'cliff_months' => 'integer',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
        'version' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(RetirementPlanVersion::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(RetirementEnrollment::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(RetirementAccount::class);
    }

    public function vestingRules(): HasMany
    {
        return $this->hasMany(RetirementVestingRule::class)->orderBy('completed_years');
    }
}
