<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpsGovernanceRule extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_governance_rules';

    protected $fillable = [
        'tenant_id',
        'policy_id',
        'rule_key',
        'rule_name',
        'rule_parameters',
        'severity_on_violation',
        'is_active',
    ];

    protected $casts = [
        'rule_parameters' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(OpsGovernancePolicy::class, 'policy_id');
    }
}
