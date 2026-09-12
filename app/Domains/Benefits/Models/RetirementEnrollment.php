<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RetirementEnrollment extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'retirement_enrollments';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'retirement_plan_id',
        'enrollment_date',
        'employee_contribution_rate',
        'employer_contribution_rate',
        'voluntary_additional_amount',
        'status',
        'created_by',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'employee_contribution_rate' => 'decimal:4',
        'employer_contribution_rate' => 'decimal:4',
        'voluntary_additional_amount' => 'decimal:4',
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
        return $this->belongsTo(RetirementPlan::class, 'retirement_plan_id');
    }
}
