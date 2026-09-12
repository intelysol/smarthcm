<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseCategory extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'expense_categories';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'category_type',
        'description',
        'max_amount',
        'receipt_threshold',
        'receipt_required',
        'is_reimbursable',
        'tax_treatment',
        'accounting_code',
        'rules',
        'is_active',
    ];

    protected $casts = [
        'max_amount' => 'decimal:4',
        'receipt_threshold' => 'decimal:4',
        'receipt_required' => 'boolean',
        'is_reimbursable' => 'boolean',
        'rules' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function claimLines(): HasMany
    {
        return $this->hasMany(ExpenseClaimLine::class, 'expense_category_id');
    }
}
