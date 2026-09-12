<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenefitEligibilityRule extends Model
{
    use HasUuids;

    protected $table = 'benefit_eligibility_rules';

    protected $fillable = [
        'tenant_id',
        'benefit_plan_id',
        'rule_name',
        'criteria',
        'is_strict',
        'is_active',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_strict' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(BenefitPlan::class, 'benefit_plan_id');
    }
}
