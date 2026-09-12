<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollAdvanceRepayment extends Model
{
    use HasUuids;

    protected $table = 'payroll_advance_repayments';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'payroll_run_id',
        'advance_reference',
        'original_advance_amount',
        'installment_amount',
        'remaining_balance',
        'currency',
        'deduction_date',
        'status',
    ];

    protected $casts = [
        'original_advance_amount' => 'decimal:4',
        'installment_amount' => 'decimal:4',
        'remaining_balance' => 'decimal:4',
        'deduction_date' => 'date',
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
