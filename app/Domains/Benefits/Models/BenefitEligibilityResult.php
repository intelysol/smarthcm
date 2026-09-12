<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenefitEligibilityResult extends Model
{
    use HasUuids;

    protected $table = 'benefit_eligibility_results';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'benefit_plan_id',
        'evaluation_date',
        'status',
        'reason',
        'criteria_evaluation',
        'effective_date',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'effective_date' => 'date',
        'criteria_evaluation' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(BenefitPlan::class, 'benefit_plan_id');
    }
}
