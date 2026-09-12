<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelAdvanceSettlement extends Model
{
    use HasUuids;

    protected $table = 'travel_advance_settlements';

    protected $fillable = [
        'tenant_id',
        'travel_advance_id',
        'expense_claim_id',
        'settled_amount',
        'remaining_balance',
        'settlement_date',
        'settlement_type',
        'finance_reference',
        'notes',
    ];

    protected $casts = [
        'settled_amount' => 'decimal:4',
        'remaining_balance' => 'decimal:4',
        'settlement_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function advance(): BelongsTo
    {
        return $this->belongsTo(TravelAdvance::class, 'travel_advance_id');
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class, 'expense_claim_id');
    }
}
