<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpsGovernancePolicy extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_governance_policies';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'category',
        'description',
        'is_enforced',
    ];

    protected $casts = [
        'is_enforced' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(OpsGovernanceRule::class, 'policy_id');
    }
}
