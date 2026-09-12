<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Payroll\Enums\RunStatus;
use App\Domains\Payroll\Enums\RunType;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollRun extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'payroll_runs';

    protected $fillable = [
        'tenant_id',
        'payroll_period_id',
        'run_number',
        'name',
        'run_type',
        'payroll_legal_entity_id',
        'currency',
        'status',
        'employee_count',
        'gross_total',
        'earnings_total',
        'deduction_total',
        'tax_total',
        'employer_cost_total',
        'net_total',
        'calculated_at',
        'locked_at',
        'approved_at',
        'approved_by',
        'posted_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'employee_count' => 'integer',
        'gross_total' => 'decimal:4',
        'earnings_total' => 'decimal:4',
        'deduction_total' => 'decimal:4',
        'tax_total' => 'decimal:4',
        'employer_cost_total' => 'decimal:4',
        'net_total' => 'decimal:4',
        'calculated_at' => 'datetime',
        'locked_at' => 'datetime',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(PayrollLegalEntity::class, 'payroll_legal_entity_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(PayrollEarning::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(PayrollDeduction::class);
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(PayrollTax::class);
    }

    public function employerContributions(): HasMany
    {
        return $this->hasMany(PayrollEmployerContribution::class);
    }

    public function calculationSnapshots(): HasMany
    {
        return $this->hasMany(PayrollCalculationSnapshot::class);
    }

    public function calculationLines(): HasMany
    {
        return $this->hasMany(PayrollCalculationLine::class);
    }

    public function variances(): HasMany
    {
        return $this->hasMany(PayrollVariance::class);
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(PayrollException::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(PayrollPayslip::class);
    }

    public function paymentBatches(): HasMany
    {
        return $this->hasMany(PayrollPaymentBatch::class);
    }

    public function accountingExports(): HasMany
    {
        return $this->hasMany(PayrollAccountingExport::class);
    }

    public function isModifiable(): bool
    {
        return in_array($this->status, [
            RunStatus::DRAFT->value,
            RunStatus::CALCULATING->value,
            RunStatus::VALIDATION_FAILED->value,
            RunStatus::CALCULATED->value,
            RunStatus::UNDER_REVIEW->value,
        ], true) && $this->locked_at === null;
    }
}
