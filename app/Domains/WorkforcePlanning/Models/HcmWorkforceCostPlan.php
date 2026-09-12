<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceCostPlan extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_cost_plans';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'department_id',
        'cost_category',
        'budgeted_amount',
        'forecast_amount',
        'actual_amount',
        'variance_amount',
        'currency',
    ];

    protected $casts = [
        'budgeted_amount' => 'decimal:2',
        'forecast_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'variance_amount' => 'decimal:2',
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
