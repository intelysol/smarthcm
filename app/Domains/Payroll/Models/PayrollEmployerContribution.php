<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollEmployerContribution extends Model
{
    use HasUuids;

    protected $table = 'payroll_employer_contributions';

    protected $fillable = [
        'tenant_id',
        'payroll_run_id',
        'employee_id',
        'contribution_code',
        'contribution_name',
        'contribution_type',
        'base_amount',
        'rate_percentage',
        'amount',
        'currency',
    ];

    protected $casts = [
        'base_amount' => 'decimal:4',
        'rate_percentage' => 'decimal:4',
        'amount' => 'decimal:4',
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
