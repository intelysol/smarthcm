<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Organization\Models\CostCenter;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExpenseClaimLine extends Model
{
    use HasUuids;

    protected $table = 'expense_claim_lines';

    protected $fillable = [
        'tenant_id',
        'expense_claim_id',
        'expense_category_id',
        'expense_date',
        'merchant',
        'description',
        'original_amount',
        'original_currency',
        'exchange_rate',
        'exchange_rate_date',
        'exchange_rate_source',
        'base_amount',
        'base_currency',
        'tax_type',
        'tax_rate',
        'tax_amount',
        'is_tax_inclusive',
        'is_tax_recoverable',
        'non_recoverable_tax',
        'project_id',
        'project_code',
        'cost_center_id',
        'department_id',
        'location',
        'payment_method',
        'corporate_card_transaction_id',
        'is_mileage',
        'mileage_distance',
        'mileage_unit',
        'mileage_rate',
        'is_per_diem',
        'per_diem_days',
        'per_diem_rate',
        'meal_deductions',
        'policy_status',
        'approved_amount',
        'approved_base_amount',
        'exception_reason',
        'override_approver_id',
        'override_at',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'original_amount' => 'decimal:4',
        'exchange_rate' => 'decimal:6',
        'exchange_rate_date' => 'date',
        'base_amount' => 'decimal:4',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'is_tax_inclusive' => 'boolean',
        'is_tax_recoverable' => 'boolean',
        'non_recoverable_tax' => 'decimal:4',
        'is_mileage' => 'boolean',
        'mileage_distance' => 'decimal:2',
        'mileage_rate' => 'decimal:4',
        'is_per_diem' => 'boolean',
        'per_diem_days' => 'decimal:2',
        'per_diem_rate' => 'decimal:4',
        'meal_deductions' => 'decimal:4',
        'approved_amount' => 'decimal:4',
        'approved_base_amount' => 'decimal:4',
        'override_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class, 'expense_claim_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function overrideApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'override_approver_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(ExpenseReceipt::class);
    }

    public function policyResults(): HasMany
    {
        return $this->hasMany(ExpensePolicyResult::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ExpenseAllocation::class);
    }

    public function corporateCardTransaction(): BelongsTo
    {
        return $this->belongsTo(CorporateCardTransaction::class, 'corporate_card_transaction_id');
    }
}
