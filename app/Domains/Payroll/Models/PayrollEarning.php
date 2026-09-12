<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollEarning extends Model
{
    use HasUuids;

    protected $table = 'payroll_earnings';

    protected $fillable = [
        'tenant_id',
        'payroll_run_id',
        'employee_id',
        'compensation_component_id',
        'earning_code',
        'earning_name',
        'earning_type',
        'rate',
        'quantity',
        'amount',
        'currency',
        'is_taxable',
        'is_pensionable',
        'calculation_source',
        'source_reference_id',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'quantity' => 'decimal:4',
        'amount' => 'decimal:4',
        'is_taxable' => 'boolean',
        'is_pensionable' => 'boolean',
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

    public function component(): BelongsTo
    {
        return $this->belongsTo(CompensationComponent::class, 'compensation_component_id');
    }
}
