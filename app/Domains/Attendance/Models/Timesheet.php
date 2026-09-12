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

class Timesheet extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'timesheets';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'attendance_period_id',
        'period_type',
        'start_date',
        'end_date',
        'total_scheduled_minutes',
        'total_worked_minutes',
        'total_regular_minutes',
        'total_overtime_minutes',
        'total_approved_overtime_minutes',
        'total_late_minutes',
        'total_early_departure_minutes',
        'total_absence_minutes',
        'total_paid_leave_minutes',
        'total_unpaid_leave_minutes',
        'total_holiday_minutes',
        'status',
        'submitted_at',
        'approved_by',
        'approved_at',
        'exported_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_scheduled_minutes' => 'integer',
        'total_worked_minutes' => 'integer',
        'total_regular_minutes' => 'integer',
        'total_overtime_minutes' => 'integer',
        'total_approved_overtime_minutes' => 'integer',
        'total_late_minutes' => 'integer',
        'total_early_departure_minutes' => 'integer',
        'total_absence_minutes' => 'integer',
        'total_paid_leave_minutes' => 'integer',
        'total_unpaid_leave_minutes' => 'integer',
        'total_holiday_minutes' => 'integer',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'exported_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(AttendancePeriod::class, 'attendance_period_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TimesheetEntry::class)->orderBy('entry_date');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function timeAllocations(): HasMany
    {
        return $this->hasMany(HcmTimeAllocation::class, 'timesheet_id');
    }

    public function overtimeTierRecords(): HasMany
    {
        return $this->hasMany(HcmOvertimeTierRecord::class, 'timesheet_id');
    }
}
