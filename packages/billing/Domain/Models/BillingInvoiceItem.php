<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingInvoiceItem extends Model
{
    use HasUuids;

    protected $table = 'billing_invoice_items';

    protected $fillable = [
        'id',
        'invoice_id',
        'description',
        'quantity',
        'unit_name',
        'unit_price',
        'amount',
        'pricing_source',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'invoice_id');
    }
}
