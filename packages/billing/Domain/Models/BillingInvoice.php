<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use App\Domains\Shared\Models\Tenant;
use Flow\Packages\Billing\Domain\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingInvoice extends Model
{
    use HasUuids;

    protected $table = 'billing_invoices';

    protected $fillable = [
        'id',
        'invoice_number',
        'tenant_id',
        'subscription_id',
        'billing_period_start',
        'billing_period_end',
        'issue_date',
        'due_date',
        'currency',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'credit_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'billing_period_start' => 'datetime',
        'billing_period_end' => 'datetime',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(BillingSubscription::class, 'subscription_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillingInvoiceItem::class, 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillingPayment::class, 'invoice_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(BillingRefund::class, 'invoice_id');
    }

    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::PAID;
    }
}
