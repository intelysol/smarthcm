<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollPaymentLine extends Model
{
    use HasUuids;

    protected $table = 'payroll_payment_lines';

    protected $fillable = [
        'tenant_id',
        'payroll_payment_batch_id',
        'employee_id',
        'payroll_payslip_id',
        'bank_name',
        'routing_number',
        'masked_account_number',
        'account_holder_name',
        'amount',
        'currency',
        'payment_status',
        'transaction_reference',
        'failure_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PayrollPaymentBatch::class, 'payroll_payment_batch_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(PayrollPayslip::class, 'payroll_payslip_id');
    }
}
