<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use Flow\Packages\Billing\Domain\Enums\EntitlementType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingPlanEntitlement extends Model
{
    use HasUuids;

    protected $table = 'billing_plan_entitlements';

    protected $fillable = [
        'id',
        'plan_id',
        'entitlement_key',
        'entitlement_type',
        'limit_value',
        'is_enabled',
        'metadata',
    ];

    protected $casts = [
        'entitlement_type' => EntitlementType::class,
        'limit_value' => 'integer',
        'is_enabled' => 'boolean',
        'metadata' => 'array',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(BillingPlan::class, 'plan_id');
    }
}
