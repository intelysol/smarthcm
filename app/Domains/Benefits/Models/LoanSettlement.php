<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanSettlement extends Model
{
    use HasUuids;

    protected $table = 'loan_settlements';

    protected $fillable = [
        'tenant_id',
        'loan_application_id',
        'outstanding_principal',
        'outstanding_interest',
        'rebate_amount',
        'final_settlement_amount',
        'settlement_date',
        'payment_method',
        'settled_by',
    ];

    protected $casts = [
        'outstanding_principal' => 'decimal:4',
        'outstanding_interest' => 'decimal:4',
        'rebate_amount' => 'decimal:4',
        'final_settlement_amount' => 'decimal:4',
        'settlement_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id');
    }

    public function settler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settled_by');
    }
}
