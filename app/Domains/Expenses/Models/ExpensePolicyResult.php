<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpensePolicyResult extends Model
{
    use HasUuids;

    protected $table = 'expense_policy_results';

    protected $fillable = [
        'tenant_id',
        'expense_claim_line_id',
        'expense_policy_id',
        'rule_name',
        'rule_type',
        'is_violation',
        'allowed_limit',
        'actual_amount',
        'excess_amount',
        'action_taken',
        'violation_details',
    ];

    protected $casts = [
        'is_violation' => 'boolean',
        'allowed_limit' => 'decimal:4',
        'actual_amount' => 'decimal:4',
        'excess_amount' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function claimLine(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaimLine::class, 'expense_claim_line_id');
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(ExpensePolicy::class, 'expense_policy_id');
    }
}
