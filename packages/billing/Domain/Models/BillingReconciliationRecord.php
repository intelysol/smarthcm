<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingReconciliationRecord extends Model
{
    use HasUuids;

    protected $table = 'billing_reconciliation_records';

    protected $fillable = [
        'id',
        'reconciliation_date',
        'invoice_id',
        'payment_id',
        'status',
        'discrepancy_type',
        'discrepancy_amount',
        'notes',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'reconciliation_date' => 'date',
        'discrepancy_amount' => 'decimal:2',
        'resolved_at' => 'datetime',
        'resolved_by' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'invoice_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(BillingPayment::class, 'payment_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
