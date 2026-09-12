<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HcmWorkforceScenario extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_workforce_scenarios';

    protected $fillable = [
        'tenant_id',
        'base_plan_id',
        'name',
        'scenario_type',
        'description',
        'status',
        'created_by',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function basePlan(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforcePlan::class, 'base_plan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assumptions(): HasMany
    {
        return $this->hasMany(HcmWorkforceScenarioAssumption::class, 'scenario_id');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(HcmWorkforceScenarioPosition::class, 'scenario_id');
    }

    public function headcounts(): HasMany
    {
        return $this->hasMany(HcmWorkforceScenarioHeadcount::class, 'scenario_id');
    }

    public function costs(): HasMany
    {
        return $this->hasMany(HcmWorkforceScenarioCost::class, 'scenario_id');
    }
}
