<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BenefitCoverage extends Model
{
    use HasUuids;

    protected $table = 'benefit_coverages';

    protected $fillable = [
        'tenant_id',
        'benefit_plan_id',
        'code',
        'name',
        'coverage_multiplier',
        'employee_cost_factor',
        'employer_cost_factor',
        'fixed_employee_cost',
        'fixed_employer_cost',
        'max_dependents',
        'is_active',
    ];

    protected $casts = [
        'coverage_multiplier' => 'decimal:4',
        'employee_cost_factor' => 'decimal:4',
        'employer_cost_factor' => 'decimal:4',
        'fixed_employee_cost' => 'decimal:4',
        'fixed_employer_cost' => 'decimal:4',
        'max_dependents' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(BenefitPlan::class, 'benefit_plan_id');
    }

    public function elections(): HasMany
    {
        return $this->hasMany(BenefitElection::class);
    }
}
