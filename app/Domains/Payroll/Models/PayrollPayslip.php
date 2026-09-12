<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollPayslip extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'payroll_payslips';

    protected $fillable = [
        'tenant_id',
        'payroll_run_id',
        'employee_id',
        'payroll_calculation_snapshot_id',
        'payslip_number',
        'pay_date',
        'period_start',
        'period_end',
        'gross_pay',
        'total_deductions',
        'total_tax',
        'net_pay',
        'ytd_gross',
        'ytd_tax',
        'ytd_net',
        'currency',
        'masked_bank_account',
        'pdf_document_id',
        'is_published',
        'published_at',
        'viewed_at',
        'created_by',
    ];

    protected $casts = [
        'pay_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'gross_pay' => 'decimal:4',
        'total_deductions' => 'decimal:4',
        'total_tax' => 'decimal:4',
        'net_pay' => 'decimal:4',
        'ytd_gross' => 'decimal:4',
        'ytd_tax' => 'decimal:4',
        'ytd_net' => 'decimal:4',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'viewed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(PayrollCalculationSnapshot::class, 'payroll_calculation_snapshot_id');
    }
}
