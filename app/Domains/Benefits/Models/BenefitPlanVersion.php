<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenefitPlanVersion extends Model
{
    use HasUuids;

    protected $table = 'benefit_plan_versions';

    protected $fillable = [
        'tenant_id',
        'benefit_plan_id',
        'version_number',
        'effective_from',
        'effective_to',
        'employee_cost',
        'employer_cost',
        'annual_limit',
        'coverage_details',
        'eligibility_criteria',
        'is_active',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'employee_cost' => 'decimal:4',
        'employer_cost' => 'decimal:4',
        'annual_limit' => 'decimal:4',
        'coverage_details' => 'array',
        'eligibility_criteria' => 'array',
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
}
