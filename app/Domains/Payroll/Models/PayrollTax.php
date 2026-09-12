<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollTax extends Model
{
    use HasUuids;

    protected $table = 'payroll_taxes';

    protected $fillable = [
        'tenant_id',
        'payroll_run_id',
        'employee_id',
        'tax_code',
        'tax_name',
        'tax_rule_version',
        'taxable_income',
        'exemptions',
        'tax_amount',
        'rebate_amount',
        'net_tax_deducted',
        'currency',
        'tax_bracket_breakdown',
    ];

    protected $casts = [
        'taxable_income' => 'decimal:4',
        'exemptions' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'rebate_amount' => 'decimal:4',
        'net_tax_deducted' => 'decimal:4',
        'tax_bracket_breakdown' => 'array',
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
}
