<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingSubscriptionItem extends Model
{
    use HasUuids;

    protected $table = 'billing_subscription_items';

    protected $fillable = [
        'id',
        'subscription_id',
        'price_id',
        'quantity',
        'unit_price',
        'status',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:4',
        'metadata' => 'array',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(BillingSubscription::class, 'subscription_id');
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(BillingPrice::class, 'price_id');
    }
}
