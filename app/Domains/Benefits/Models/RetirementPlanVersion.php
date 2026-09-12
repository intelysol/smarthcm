<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetirementPlanVersion extends Model
{
    use HasUuids;

    protected $table = 'retirement_plan_versions';

    protected $fillable = [
        'tenant_id',
        'retirement_plan_id',
        'version_number',
        'effective_from',
        'effective_to',
        'employee_rate',
        'employer_rate',
        'vesting_schedule',
        'is_active',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'employee_rate' => 'decimal:4',
        'employer_rate' => 'decimal:4',
        'vesting_schedule' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(RetirementPlan::class, 'retirement_plan_id');
    }
}
