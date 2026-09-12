<?php

namespace App\Domains\Absence\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HcmAbsencePeriod extends Model
{
    use HasUuids;

    protected $table = 'hcm_absence_periods';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'start_date',
        'end_date',
        'working_days_count',
        'calendar_days_count',
        'total_absence_hours',
        'absence_category',
        'is_long_term',
        'status',
        'expected_return_date',
        'actual_return_date',
        'leave_application_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',
        'working_days_count' => 'integer',
        'calendar_days_count' => 'integer',
        'total_absence_hours' => 'decimal:2',
        'is_long_term' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function returnPlan(): HasOne
    {
        return $this->hasOne(HcmAbsenceReturnToWorkPlan::class, 'absence_period_id');
    }

    public function cases(): HasMany
    {
        return $this->hasMany(HcmAbsenceCase::class, 'absence_period_id');
    }
}