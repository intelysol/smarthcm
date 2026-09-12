<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceScenarioPosition extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_scenario_positions';

    protected $fillable = [
        'tenant_id',
        'scenario_id',
        'action_type',
        'position_plan_id',
        'title',
        'target_department_id',
        'cost_delta',
    ];

    protected $casts = [
        'cost_delta' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceScenario::class, 'scenario_id');
    }

    public function positionPlan(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforcePositionPlan::class, 'position_plan_id');
    }

    public function targetDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'target_department_id');
    }
}
