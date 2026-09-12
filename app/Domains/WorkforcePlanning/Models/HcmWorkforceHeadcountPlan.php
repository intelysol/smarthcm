<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceHeadcountPlan extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_headcount_plans';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'period_id',
        'department_id',
        'branch_id',
        'opening_headcount',
        'planned_hires',
        'transfers_in',
        'planned_exits',
        'transfers_out',
        'closing_headcount',
        'planned_fte',
    ];

    protected $casts = [
        'opening_headcount' => 'integer',
        'planned_hires' => 'integer',
        'transfers_in' => 'integer',
        'planned_exits' => 'integer',
        'transfers_out' => 'integer',
        'closing_headcount' => 'integer',
        'planned_fte' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforcePlan::class, 'plan_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforcePlanPeriod::class, 'period_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
