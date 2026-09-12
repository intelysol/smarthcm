<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BenefitWaiver extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'benefit_waivers';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'benefit_plan_id',
        'benefit_enrollment_window_id',
        'reason',
        'supporting_document_id',
        'waiver_date',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'waiver_date' => 'date',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
