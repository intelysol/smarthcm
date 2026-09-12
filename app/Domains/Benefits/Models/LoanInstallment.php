<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanInstallment extends Model
{
    use HasUuids;

    protected $table = 'loan_installments';

    protected $fillable = [
        'tenant_id',
        'loan_schedule_id',
        'employee_id',
        'installment_number',
        'due_date',
        'principal_amount',
        'interest_amount',
        'total_installment',
        'paid_amount',
        'balance_remaining',
        'status',
        'payroll_run_id',
        'paid_date',
    ];

    protected $casts = [
        'installment_number' => 'integer',
        'due_date' => 'date',
        'principal_amount' => 'decimal:4',
        'interest_amount' => 'decimal:4',
        'total_installment' => 'decimal:4',
        'paid_amount' => 'decimal:4',
        'balance_remaining' => 'decimal:4',
        'paid_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(LoanSchedule::class, 'loan_schedule_id');
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
