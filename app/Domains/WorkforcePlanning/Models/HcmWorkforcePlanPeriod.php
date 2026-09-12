<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmWorkforcePlanPeriod extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_plan_periods';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'period_name',
        'period_sequence',
        'start_date',
        'end_date',
        'is_closed',
    ];

    protected $casts = [
        'period_sequence' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_closed' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforcePlan::class, 'plan_id');
    }

    public function headcountPlans(): HasMany
    {
        return $this->hasMany(HcmWorkforceHeadcountPlan::class, 'period_id');
    }
}
