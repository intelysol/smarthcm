<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpensePolicyAssignment extends Model
{
    use HasUuids;

    protected $table = 'expense_policy_assignments';

    protected $fillable = [
        'tenant_id',
        'expense_policy_id',
        'scope_type',
        'scope_id',
        'priority',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(ExpensePolicy::class, 'expense_policy_id');
    }
}
