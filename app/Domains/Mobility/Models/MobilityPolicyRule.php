<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityPolicyRule extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_policy_rules';

    protected $fillable = [
        'tenant_id',
        'policy_version_id',
        'rule_category',
        'rule_name',
        'conditions',
        'actions',
    ];

    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function policyVersion(): BelongsTo
    {
        return $this->belongsTo(MobilityPolicyVersion::class, 'policy_version_id');
    }
}
