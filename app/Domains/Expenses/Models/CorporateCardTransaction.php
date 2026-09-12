<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CorporateCardTransaction extends Model
{
    use HasUuids;

    protected $table = 'corporate_card_transactions';

    protected $fillable = [
        'tenant_id',
        'corporate_card_id',
        'employee_id',
        'transaction_reference',
        'transaction_date',
        'merchant_name',
        'amount',
        'currency',
        'base_amount',
        'base_currency',
        'exchange_rate',
        'category_hint',
        'is_matched',
        'matched_claim_line_id',
        'match_status',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:4',
        'base_amount' => 'decimal:4',
        'exchange_rate' => 'decimal:6',
        'is_matched' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(CorporateCard::class, 'corporate_card_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function matchedClaimLine(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaimLine::class, 'matched_claim_line_id');
    }

    public function matchRecord(): HasOne
    {
        return $this->hasOne(CorporateCardMatch::class);
    }
}
