<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use Flow\Packages\Billing\Domain\Enums\BillingInterval;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingPlan extends Model
{
    use HasUuids;

    protected $table = 'billing_plans';

    protected $fillable = [
        'id',
        'product_id',
        'version_id',
        'name',
        'code',
        'description',
        'billing_model',
        'billing_interval',
        'currency',
        'base_price',
        'setup_fee',
        'trial_period_days',
        'min_commitment_months',
        'max_quantity',
        'status',
        'version',
        'metadata',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'trial_period_days' => 'integer',
        'min_commitment_months' => 'integer',
        'max_quantity' => 'integer',
        'version' => 'integer',
        'metadata' => 'array',
        'billing_interval' => BillingInterval::class,
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(BillingProduct::class, 'product_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(BillingProductVersion::class, 'version_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(BillingPrice::class, 'plan_id');
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(BillingPlanEntitlement::class, 'plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(BillingSubscription::class, 'plan_id');
    }
}
