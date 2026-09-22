<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingCreditTransaction extends Model
{
    use HasUuids;

    protected $table = 'billing_credit_transactions';

    protected $fillable = [
        'id',
        'credit_id',
        'invoice_id',
        'amount',
        'type',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function credit(): BelongsTo
    {
        return $this->belongsTo(BillingCredit::class, 'credit_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'invoice_id');
    }
}
