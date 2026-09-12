<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsurancePremium extends Model
{
    use HasUuids;

    protected $table = 'insurance_premiums';

    protected $fillable = [
        'tenant_id',
        'insurance_coverage_id',
        'payroll_period_id',
        'employee_amount',
        'employer_amount',
        'due_date',
        'status',
    ];

    protected $casts = [
        'employee_amount' => 'decimal:4',
        'employer_amount' => 'decimal:4',
        'due_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function coverage(): BelongsTo
    {
        return $this->belongsTo(InsuranceCoverage::class, 'insurance_coverage_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }
}
