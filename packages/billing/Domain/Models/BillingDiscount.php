<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingDiscount extends Model
{
    use HasUuids;

    protected $table = 'billing_discounts';

    protected $fillable = [
        'id',
        'code',
        'name',
        'discount_type',
        'value',
        'max_redemptions',
        'redeemed_count',
        'starts_at',
        'expires_at',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_redemptions' => 'integer',
        'redeemed_count' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(BillingDiscountRedemption::class, 'discount_id');
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        if ($this->max_redemptions !== null && $this->redeemed_count >= $this->max_redemptions) {
            return false;
        }

        return true;
    }
}
