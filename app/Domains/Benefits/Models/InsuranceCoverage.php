<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceCoverage extends Model
{
    use HasUuids;

    protected $table = 'insurance_coverages';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'insurance_policy_id',
        'benefit_enrollment_id',
        'certificate_number',
        'coverage_tier',
        'sum_insured',
        'employee_premium',
        'employer_premium',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected $casts = [
        'sum_insured' => 'decimal:4',
        'employee_premium' => 'decimal:4',
        'employer_premium' => 'decimal:4',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(InsurancePolicy::class, 'insurance_policy_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(BenefitEnrollment::class, 'benefit_enrollment_id');
    }

    public function premiums(): HasMany
    {
        return $this->hasMany(InsurancePremium::class);
    }
}
