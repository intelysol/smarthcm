<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceScenarioAssumption extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_scenario_assumptions';

    protected $fillable = [
        'tenant_id',
        'scenario_id',
        'assumption_key',
        'override_value',
    ];

    protected $casts = [
        'override_value' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceScenario::class, 'scenario_id');
    }
}
