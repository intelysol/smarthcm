<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseExchangeRate extends Model
{
    use HasUuids;

    protected $table = 'expense_exchange_rates';

    protected $fillable = [
        'tenant_id',
        'from_currency',
        'to_currency',
        'rate',
        'effective_date',
        'source',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'effective_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
