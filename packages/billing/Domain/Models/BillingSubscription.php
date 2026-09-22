<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use App\Domains\Shared\Models\Tenant;
use Flow\Packages\Billing\Domain\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingSubscription extends Model
{
    use HasUuids;

    protected $table = 'billing_subscriptions';

    protected $fillable = [
        'id',
        'tenant_id',
        'plan_id',
        'status',
        'quantity',
        'currency',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'grace_ends_at',
        'billing_anchor_day',
        'current_cycle_start',
        'current_cycle_end',
        'paused_at',
        'suspended_at',
        'cancelled_at',
        'cancellation_reason',
        'auto_renew',
        'metadata',
    ];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'quantity' => 'integer',
        'billing_anchor_day' => 'integer',
        'auto_renew' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'current_cycle_start' => 'datetime',
        'current_cycle_end' => 'datetime',
        'paused_at' => 'datetime',
        'suspended_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(BillingPlan::class, 'plan_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillingSubscriptionItem::class, 'subscription_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(BillingInvoice::class, 'subscription_id');
    }

    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE;
    }

    public function isTrialing(): bool
    {
        return $this->status === SubscriptionStatus::TRIALING;
    }

    public function isUsable(): bool
    {
        return $this->status->isUsable();
    }
}
