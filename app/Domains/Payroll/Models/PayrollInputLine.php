<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollInputLine extends Model
{
    use HasUuids;

    protected $table = 'payroll_input_lines';

    protected $fillable = [
        'tenant_id',
        'payroll_input_id',
        'employee_id',
        'source_module',
        'source_entity_type',
        'source_entity_id',
        'input_type',
        'quantity',
        'rate',
        'amount',
        'currency',
        'effective_date',
        'approval_status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'rate' => 'decimal:4',
        'amount' => 'decimal:4',
        'effective_date' => 'date',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function payrollInput(): BelongsTo
    {
        return $this->belongsTo(PayrollInput::class, 'payroll_input_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
