<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Benefits\Enums\EnrollmentStatus;
use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BenefitEnrollment extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'benefit_enrollments';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'benefit_plan_id',
        'benefit_enrollment_window_id',
        'benefit_plan_version_id',
        'enrollment_type',
        'coverage_level',
        'effective_from',
        'effective_to',
        'employee_contribution',
        'employer_contribution',
        'currency',
        'status',
        'workflow_instance_id',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'employee_contribution' => 'decimal:4',
        'employer_contribution' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(BenefitPlan::class, 'benefit_plan_id');
    }

    public function window(): BelongsTo
    {
        return $this->belongsTo(BenefitEnrollmentWindow::class, 'benefit_enrollment_window_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(BenefitPlanVersion::class, 'benefit_plan_version_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(BenefitDependent::class);
    }

    public function beneficiaries(): HasMany
    {
        return $this->hasMany(BenefitBeneficiary::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(BenefitContribution::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(InsuranceClaim::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [EnrollmentStatus::APPROVED->value, EnrollmentStatus::ACTIVE->value], true);
    }
}
