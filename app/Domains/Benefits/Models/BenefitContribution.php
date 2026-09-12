<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenefitContribution extends Model
{
    use HasUuids;

    protected $table = 'benefit_contributions';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'benefit_enrollment_id',
        'payroll_period_id',
        'employee_amount',
        'employer_amount',
        'currency',
        'contribution_date',
        'status',
    ];

    protected $casts = [
        'employee_amount' => 'decimal:4',
        'employer_amount' => 'decimal:4',
        'contribution_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(BenefitEnrollment::class, 'benefit_enrollment_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }
}
