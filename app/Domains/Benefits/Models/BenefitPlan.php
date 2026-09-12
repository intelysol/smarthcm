<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BenefitPlan extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'benefit_plans';

    protected $fillable = [
        'tenant_id',
        'benefit_program_id',
        'benefit_category_id',
        'benefit_provider_id',
        'code',
        'name',
        'benefit_type',
        'coverage_level',
        'currency',
        'employee_cost',
        'employer_cost',
        'annual_limit',
        'monthly_limit',
        'min_coverage',
        'max_coverage',
        'waiting_period_days',
        'requires_beneficiary',
        'requires_dependents',
        'is_mandatory',
        'is_waivable',
        'enrollment_type',
        'version',
        'description',
        'effective_from',
        'effective_to',
        'coverage',
        'eligibility_rules',
        'contribution_rules',
        'tax_rules',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'employee_cost' => 'decimal:4',
        'employer_cost' => 'decimal:4',
        'annual_limit' => 'decimal:4',
        'monthly_limit' => 'decimal:4',
        'min_coverage' => 'decimal:4',
        'max_coverage' => 'decimal:4',
        'waiting_period_days' => 'integer',
        'requires_beneficiary' => 'boolean',
        'requires_dependents' => 'boolean',
        'is_mandatory' => 'boolean',
        'is_waivable' => 'boolean',
        'version' => 'integer',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'coverage' => 'array',
        'eligibility_rules' => 'array',
        'contribution_rules' => 'array',
        'tax_rules' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(BenefitProgram::class, 'benefit_program_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BenefitCategory::class, 'benefit_category_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(BenefitProvider::class, 'benefit_provider_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(BenefitPlanVersion::class);
    }

    public function coverages(): HasMany
    {
        return $this->hasMany(BenefitCoverage::class);
    }

    public function eligibilityRules(): HasMany
    {
        return $this->hasMany(BenefitEligibilityRule::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(BenefitEnrollment::class);
    }

    public function elections(): HasMany
    {
        return $this->hasMany(BenefitElection::class);
    }

    public function waivers(): HasMany
    {
        return $this->hasMany(BenefitWaiver::class);
    }

    public function providerMappings(): HasMany
    {
        return $this->hasMany(BenefitProviderMapping::class);
    }
}
