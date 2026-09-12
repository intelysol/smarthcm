<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforcePlanAssumption extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_plan_assumptions';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'assumption_key',
        'name',
        'data_type',
        'value',
        'notes',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'effective_from' => 'date',
        'effective_to' => 'date',
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
