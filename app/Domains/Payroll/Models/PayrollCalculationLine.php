<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollCalculationLine extends Model
{
    use HasUuids;

    protected $table = 'payroll_calculation_lines';

    protected $fillable = [
        'tenant_id',
        'payroll_calculation_snapshot_id',
        'payroll_run_id',
        'employee_id',
        'line_category',
        'line_code',
        'line_name',
        'amount',
        'currency',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(PayrollCalculationSnapshot::class, 'payroll_calculation_snapshot_id');
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
