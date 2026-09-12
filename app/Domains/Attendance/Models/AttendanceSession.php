<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceSession extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'attendance_sessions';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'session_date',
        'work_calendar_id',
        'shift_definition_id',
        'roster_assignment_id',
        'scheduled_start_time',
        'scheduled_end_time',
        'scheduled_minutes',
        'actual_start_time',
        'actual_end_time',
        'gross_duration_minutes',
        'total_break_minutes',
        'unpaid_break_minutes',
        'paid_break_minutes',
        'net_worked_minutes',
        'regular_minutes',
        'overtime_minutes',
        'approved_overtime_minutes',
        'late_minutes',
        'early_departure_minutes',
        'undertime_minutes',
        'status',
        'is_overnight',
        'is_split',
        'is_flexible',
        'is_adjusted',
        'is_approved',
        'approved_by',
        'approved_at',
        'processed_at',
    ];

    protected $casts = [
        'session_date' => 'date',
        'scheduled_start_time' => 'datetime',
        'scheduled_end_time' => 'datetime',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'scheduled_minutes' => 'integer',
        'gross_duration_minutes' => 'integer',
        'total_break_minutes' => 'integer',
        'unpaid_break_minutes' => 'integer',
        'paid_break_minutes' => 'integer',
        'net_worked_minutes' => 'integer',
        'regular_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'approved_overtime_minutes' => 'integer',
        'late_minutes' => 'integer',
        'early_departure_minutes' => 'integer',
        'undertime_minutes' => 'integer',
        'is_overnight' => 'boolean',
        'is_split' => 'boolean',
        'is_flexible' => 'boolean',
        'is_adjusted' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(WorkCalendar::class, 'work_calendar_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftDefinition::class, 'shift_definition_id');
    }

    public function rosterAssignment(): BelongsTo
    {
        return $this->belongsTo(RosterAssignment::class, 'roster_assignment_id');
    }

    public function sessionBreaks(): HasMany
    {
        return $this->hasMany(AttendanceSessionBreak::class);
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(AttendanceException::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(AttendanceAdjustment::class);
    }

    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function overtimeTierRecords(): HasMany
    {
        return $this->hasMany(HcmOvertimeTierRecord::class, 'attendance_session_id');
    }

    public function complianceChecks(): HasMany
    {
        return $this->hasMany(HcmLaborComplianceCheck::class, 'attendance_session_id');
    }
}
