<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseException extends Model
{
    use HasUuids;

    protected $table = 'expense_exceptions';

    protected $fillable = [
        'tenant_id',
        'expense_claim_id',
        'expense_claim_line_id',
        'exception_type',
        'severity',
        'reason',
        'status',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class, 'expense_claim_id');
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaimLine::class, 'expense_claim_line_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
