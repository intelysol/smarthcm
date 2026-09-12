<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpensePolicyVersion extends Model
{
    use HasUuids;

    protected $table = 'expense_policy_versions';

    protected $fillable = [
        'tenant_id',
        'expense_policy_id',
        'version_number',
        'effective_from',
        'effective_to',
        'daily_meal_limit',
        'daily_hotel_limit',
        'receipt_required_threshold',
        'allow_policy_override',
        'rules_configuration',
        'is_active',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'daily_meal_limit' => 'decimal:4',
        'daily_hotel_limit' => 'decimal:4',
        'receipt_required_threshold' => 'decimal:4',
        'allow_policy_override' => 'boolean',
        'rules_configuration' => 'array',
        'is_active' => 'boolean',
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
