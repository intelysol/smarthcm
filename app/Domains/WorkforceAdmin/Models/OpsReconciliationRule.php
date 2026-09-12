<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpsReconciliationRule extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_reconciliation_rules';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'source_domain',
        'target_domain',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(OpsReconciliationResult::class, 'rule_id');
    }
}
