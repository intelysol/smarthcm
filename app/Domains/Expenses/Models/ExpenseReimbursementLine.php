<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseReimbursementLine extends Model
{
    use HasUuids;

    protected $table = 'expense_reimbursement_lines';

    protected $fillable = [
        'tenant_id',
        'expense_reimbursement_id',
        'expense_claim_id',
        'claimed_amount',
        'approved_amount',
        'advance_deduction_amount',
        'payable_amount',
        'currency',
    ];

    protected $casts = [
        'claimed_amount' => 'decimal:4',
        'approved_amount' => 'decimal:4',
        'advance_deduction_amount' => 'decimal:4',
        'payable_amount' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reimbursement(): BelongsTo
    {
        return $this->belongsTo(ExpenseReimbursement::class, 'expense_reimbursement_id');
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class, 'expense_claim_id');
    }
}
