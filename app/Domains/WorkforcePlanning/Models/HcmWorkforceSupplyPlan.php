<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceSupplyPlan extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_supply_plans';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'department_id',
        'current_headcount',
        'expected_retirements',
        'expected_attrition',
        'expected_transfers_out',
        'expected_transfers_in',
        'expected_internal_hires',
        'external_hiring_required',
        'projected_closing_headcount',
    ];

    protected $casts = [
        'current_headcount' => 'integer',
        'expected_retirements' => 'integer',
        'expected_attrition' => 'integer',
        'expected_transfers_out' => 'integer',
        'expected_transfers_in' => 'integer',
        'expected_internal_hires' => 'integer',
        'external_hiring_required' => 'integer',
        'projected_closing_headcount' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforcePlan::class, 'plan_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
