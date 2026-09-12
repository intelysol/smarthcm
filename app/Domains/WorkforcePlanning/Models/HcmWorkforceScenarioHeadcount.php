<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceScenarioHeadcount extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_scenario_headcount';

    protected $fillable = [
        'tenant_id',
        'scenario_id',
        'period_name',
        'projected_headcount',
        'projected_fte',
        'projected_hires',
        'projected_exits',
    ];

    protected $casts = [
        'projected_headcount' => 'integer',
        'projected_fte' => 'decimal:2',
        'projected_hires' => 'integer',
        'projected_exits' => 'integer',
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
