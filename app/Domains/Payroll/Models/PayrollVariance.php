<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollVariance extends Model
{
    use HasUuids;

    protected $table = 'payroll_variances';

    protected $fillable = [
        'tenant_id',
        'payroll_run_id',
        'employee_id',
        'current_gross',
        'previous_gross',
        'gross_variance_amount',
        'gross_variance_percentage',
        'current_net',
        'previous_net',
        'net_variance_amount',
        'variance_type',
        'is_flagged',
        'explanation',
    ];

    protected $casts = [
        'current_gross' => 'decimal:4',
        'previous_gross' => 'decimal:4',
        'gross_variance_amount' => 'decimal:4',
        'gross_variance_percentage' => 'decimal:4',
        'current_net' => 'decimal:4',
        'previous_net' => 'decimal:4',
        'net_variance_amount' => 'decimal:4',
        'is_flagged' => 'boolean',
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
