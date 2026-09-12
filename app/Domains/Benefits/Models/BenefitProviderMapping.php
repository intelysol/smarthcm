<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenefitProviderMapping extends Model
{
    use HasUuids;

    protected $table = 'benefit_provider_mappings';

    protected $fillable = [
        'tenant_id',
        'benefit_provider_id',
        'benefit_plan_id',
        'external_plan_code',
        'external_plan_name',
        'policy_number',
        'group_number',
        'effective_from',
        'effective_to',
        'sync_status',
        'last_exported_at',
        'mapping_metadata',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'last_exported_at' => 'datetime',
        'mapping_metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(BenefitProvider::class, 'benefit_provider_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(BenefitPlan::class, 'benefit_plan_id');
    }
}
