<?php

namespace App\Domains\Absence\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\HealthSafety\Models\HcmReturnToWorkCase;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmAbsenceReturnToWorkPlan extends Model
{
    use HasUuids;

    protected $table = 'hcm_absence_return_to_work_plans';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'absence_period_id',
        'health_safety_case_id',
        'expected_return_date',
        'actual_return_date',
        'return_phase',
        'capacity_percentage',
        'operational_restrictions',
        'review_date',
        'responsible_manager_id',
        'hr_owner_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',
        'review_date' => 'date',
        'capacity_percentage' => 'decimal:2',
        'operational_restrictions' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function absencePeriod(): BelongsTo
    {
        return $this->belongsTo(HcmAbsencePeriod::class, 'absence_period_id');
    }

    public function healthSafetyCase(): BelongsTo
    {
        return $this->belongsTo(HcmReturnToWorkCase::class, 'health_safety_case_id');
    }

    public function responsibleManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_manager_id');
    }

    public function hrOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_owner_id');
    }
}