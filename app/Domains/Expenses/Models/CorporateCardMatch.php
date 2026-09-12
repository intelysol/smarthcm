<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorporateCardMatch extends Model
{
    use HasUuids;

    protected $table = 'corporate_card_matches';

    protected $fillable = [
        'tenant_id',
        'corporate_card_transaction_id',
        'expense_claim_line_id',
        'matched_amount',
        'match_confidence',
        'matched_by',
        'matched_at',
    ];

    protected $casts = [
        'matched_amount' => 'decimal:4',
        'match_confidence' => 'decimal:2',
        'matched_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(CorporateCardTransaction::class, 'corporate_card_transaction_id');
    }

    public function claimLine(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaimLine::class, 'expense_claim_line_id');
    }

    public function matcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_by');
    }
}
