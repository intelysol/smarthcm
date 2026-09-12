<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseReimbursement extends Model
{
    use HasUuids;

    protected $table = 'expense_reimbursements';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'reimbursement_number',
        'total_reimbursement_amount',
        'currency',
        'reimbursement_method',
        'payment_batch_id',
        'payroll_period_id',
        'status',
        'payment_reference',
        'payment_date',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
    ];

    protected $casts = [
        'total_reimbursement_amount' => 'decimal:4',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ExpenseReimbursementLine::class);
    }
}
