<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollLoanRepayment extends Model
{
    use HasUuids;

    protected $table = 'payroll_loan_repayments';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'employee_loan_id',
        'payroll_run_id',
        'installment_amount',
        'principal_amount',
        'interest_amount',
        'remaining_balance',
        'currency',
        'payment_date',
        'status',
    ];

    protected $casts = [
        'installment_amount' => 'decimal:4',
        'principal_amount' => 'decimal:4',
        'interest_amount' => 'decimal:4',
        'remaining_balance' => 'decimal:4',
        'payment_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }
}
