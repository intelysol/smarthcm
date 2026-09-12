<?php

namespace App\Domains\Absence\Models;

use App\Domains\Attendance\Models\AttendanceException;
use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmAbsenceEvent extends Model
{
    use HasUuids;

    protected $table = 'hcm_absence_events';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'absence_date',
        'start_time',
        'end_time',
        'duration_hours',
        'absence_category',
        'source',
        'leave_application_id',
        'attendance_exception_id',
        'is_planned',
        'status',
        'reported_at',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'absence_date' => 'date',
        'duration_hours' => 'decimal:2',
        'is_planned' => 'boolean',
        'reported_at' => 'datetime',
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

    public function attendanceException(): BelongsTo
    {
        return $this->belongsTo(AttendanceException::class, 'attendance_exception_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function impacts(): HasMany
    {
        return $this->hasMany(HcmAbsenceOperationalImpact::class, 'absence_event_id');
    }
}