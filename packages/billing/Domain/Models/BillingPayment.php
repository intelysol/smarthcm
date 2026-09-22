<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use App\Domains\Shared\Models\Tenant;
use Flow\Packages\Billing\Domain\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingPayment extends Model
{
    use HasUuids;

    protected $table = 'billing_payments';

    protected $fillable = [
        'id',
        'payment_number',
        'tenant_id',
        'invoice_id',
        'provider',
        'provider_transaction_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'failure_reason',
        'initiated_at',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'amount' => 'decimal:2',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'invoice_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(BillingRefund::class, 'payment_id');
    }
}
