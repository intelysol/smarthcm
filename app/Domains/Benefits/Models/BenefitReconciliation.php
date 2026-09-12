<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenefitReconciliation extends Model
{
    use HasUuids;

    protected $table = 'benefit_reconciliations';

    protected $fillable = [
        'tenant_id',
        'payroll_period_id',
        'benefit_enrollment_id',
        'employee_id',
        'benefit_amount',
        'payroll_deduction_amount',
        'variance',
        'status',
        'reconciled_at',
        'notes',
    ];

    protected $casts = [
        'benefit_amount' => 'decimal:4',
        'payroll_deduction_amount' => 'decimal:4',
        'variance' => 'decimal:4',
        'reconciled_at' => 'datetime',
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
}
