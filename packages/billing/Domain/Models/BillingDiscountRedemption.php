<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingDiscountRedemption extends Model
{
    use HasUuids;

    protected $table = 'billing_discount_redemptions';

    protected $fillable = [
        'id',
        'discount_id',
        'tenant_id',
        'invoice_id',
        'amount_discounted',
        'redeemed_at',
    ];

    protected $casts = [
        'amount_discounted' => 'decimal:2',
        'redeemed_at' => 'datetime',
    ];

    public function discount(): BelongsTo
    {
        return $this->belongsTo(BillingDiscount::class, 'discount_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'invoice_id');
    }
}
