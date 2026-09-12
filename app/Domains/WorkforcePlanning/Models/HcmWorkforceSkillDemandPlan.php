<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceSkillDemandPlan extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_skill_demand_plans';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'skill_name',
        'current_supply_count',
        'required_count',
        'skill_gap_count',
        'planned_hiring_count',
        'planned_upskilling_count',
        'planned_mobility_count',
        'planned_contractor_count',
    ];

    protected $casts = [
        'current_supply_count' => 'integer',
        'required_count' => 'integer',
        'skill_gap_count' => 'integer',
        'planned_hiring_count' => 'integer',
        'planned_upskilling_count' => 'integer',
        'planned_mobility_count' => 'integer',
        'planned_contractor_count' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforcePlan::class, 'plan_id');
    }
}
