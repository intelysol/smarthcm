<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDeduction extends Model
{
    use HasUuids;

    protected $table = 'payroll_deductions';

    protected $fillable = [
        'tenant_id',
        'payroll_run_id',
        'employee_id',
        'compensation_component_id',
        'deduction_code',
        'deduction_name',
        'deduction_type',
        'amount',
        'currency',
        'priority_order',
        'calculation_source',
        'source_reference_id',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'priority_order' => 'integer',
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
