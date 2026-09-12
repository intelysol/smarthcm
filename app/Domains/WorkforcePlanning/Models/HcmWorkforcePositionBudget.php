<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforcePositionBudget extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_position_budgets';

    protected $fillable = [
        'tenant_id',
        'position_plan_id',
        'base_salary_budget',
        'bonus_budget',
        'benefits_budget',
        'employer_contributions_budget',
        'payroll_tax_budget',
        'recruitment_cost_budget',
        'equipment_cost_budget',
        'total_employment_cost',
        'currency',
    ];

    protected $casts = [
        'base_salary_budget' => 'decimal:2',
        'bonus_budget' => 'decimal:2',
        'benefits_budget' => 'decimal:2',
        'employer_contributions_budget' => 'decimal:2',
        'payroll_tax_budget' => 'decimal:2',
        'recruitment_cost_budget' => 'decimal:2',
        'equipment_cost_budget' => 'decimal:2',
        'total_employment_cost' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function positionPlan(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforcePositionPlan::class, 'position_plan_id');
    }
}
