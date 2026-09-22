<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingRefund extends Model
{
    use HasUuids;

    protected $table = 'billing_refunds';

    protected $fillable = [
        'id',
        'refund_number',
        'payment_id',
        'invoice_id',
        'tenant_id',
        'amount',
        'currency',
        'reason',
        'status',
        'provider_refund_id',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(BillingPayment::class, 'payment_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'invoice_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
