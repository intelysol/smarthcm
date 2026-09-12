<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollCalculationSnapshot extends Model
{
    use HasUuids;

    protected $table = 'payroll_calculation_snapshots';

    protected $fillable = [
        'tenant_id',
        'payroll_run_id',
        'employee_id',
        'gross_pay',
        'total_deductions',
        'total_tax',
        'total_employer_cost',
        'net_pay',
        'currency',
        'rounding_method',
        'employee_snapshot',
        'compensation_snapshot',
        'inputs_snapshot',
        'earnings_snapshot',
        'deductions_snapshot',
        'taxes_snapshot',
        'employer_contributions_snapshot',
        'tax_rule_version',
        'policy_version',
        'idempotency_key',
        'calculated_at',
    ];

    protected $casts = [
        'gross_pay' => 'decimal:4',
        'total_deductions' => 'decimal:4',
        'total_tax' => 'decimal:4',
        'total_employer_cost' => 'decimal:4',
        'net_pay' => 'decimal:4',
        'employee_snapshot' => 'array',
        'compensation_snapshot' => 'array',
        'inputs_snapshot' => 'array',
        'earnings_snapshot' => 'array',
        'deductions_snapshot' => 'array',
        'taxes_snapshot' => 'array',
        'employer_contributions_snapshot' => 'array',
        'calculated_at' => 'datetime',
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

    public function lines(): HasMany
    {
        return $this->hasMany(PayrollCalculationLine::class);
    }
}
