<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceScenarioCost extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_scenario_costs';

    protected $fillable = [
        'tenant_id',
        'scenario_id',
        'cost_category',
        'projected_cost',
        'variance_vs_base',
    ];

    protected $casts = [
        'projected_cost' => 'decimal:2',
        'variance_vs_base' => 'decimal:2',
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
