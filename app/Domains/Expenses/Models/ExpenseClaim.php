<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseClaim extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'expense_claims';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'travel_authorization_id',
        'travel_request_id',
        'policy_id',
        'claim_number',
        'title',
        'claim_date',
        'claimed_total',
        'approved_total',
        'advance_settled_amount',
        'net_reimbursement_amount',
        'currency',
        'base_currency',
        'exchange_rate',
        'status',
        'approval_level',
        'approved_by',
        'approved_at',
        'finance_reviewed_by',
        'finance_reviewed_at',
        'is_period_locked',
        'workflow_instance_id',
        'notes',
    ];

    protected $casts = [
        'claim_date' => 'date',
        'claimed_total' => 'decimal:4',
        'approved_total' => 'decimal:4',
        'advance_settled_amount' => 'decimal:4',
        'net_reimbursement_amount' => 'decimal:4',
        'exchange_rate' => 'decimal:6',
        'approval_level' => 'integer',
        'approved_at' => 'datetime',
        'finance_reviewed_at' => 'datetime',
        'is_period_locked' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function travelAuthorization(): BelongsTo
    {
        return $this->belongsTo(TravelAuthorization::class);
    }

    public function travelRequest(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(ExpensePolicy::class, 'policy_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function financeReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_reviewed_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ExpenseClaimLine::class);
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ExpenseException::class);
    }

    public function advanceSettlements(): HasMany
    {
        return $this->hasMany(TravelAdvanceSettlement::class);
    }

    public function recalculateTotals(): void
    {
        $claimed = $this->lines()->sum('base_amount');
        $approved = $this->lines()->where('policy_status', '!=', 'violation_blocked')->sum('approved_base_amount');
        $advanceOffset = (float) $this->advance_settled_amount;
        $net = max(0.0, (float) $approved - $advanceOffset);

        $this->update([
            'claimed_total' => $claimed,
            'approved_total' => $approved,
            'net_reimbursement_amount' => $net,
        ]);
    }
}
