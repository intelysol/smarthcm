<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetirementVestingRule extends Model
{
    use HasUuids;

    protected $table = 'retirement_vesting_rules';

    protected $fillable = [
        'tenant_id',
        'retirement_plan_id',
        'completed_years',
        'vesting_percentage',
    ];

    protected $casts = [
        'completed_years' => 'integer',
        'vesting_percentage' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(RetirementPlan::class, 'retirement_plan_id');
    }
}
