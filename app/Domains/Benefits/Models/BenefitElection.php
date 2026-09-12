<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BenefitElection extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'benefit_elections';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'benefit_plan_id',
        'benefit_enrollment_window_id',
        'benefit_life_event_id',
        'benefit_coverage_id',
        'coverage_level',
        'election_date',
        'effective_date',
        'employee_cost_estimated',
        'employer_cost_estimated',
        'total_cost_estimated',
        'currency',
        'status',
        'is_waived',
        'waiver_reason',
        'supporting_document_id',
        'selected_dependents',
        'beneficiaries_data',
        'notes',
        'confirmed_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'election_date' => 'date',
        'effective_date' => 'date',
        'employee_cost_estimated' => 'decimal:4',
        'employer_cost_estimated' => 'decimal:4',
        'total_cost_estimated' => 'decimal:4',
        'is_waived' => 'boolean',
        'selected_dependents' => 'array',
        'beneficiaries_data' => 'array',
        'confirmed_at' => 'datetime',
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

    public function lifeEvent(): BelongsTo
    {
        return $this->belongsTo(BenefitLifeEvent::class, 'benefit_life_event_id');
    }

    public function coverage(): BelongsTo
    {
        return $this->belongsTo(BenefitCoverage::class, 'benefit_coverage_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
