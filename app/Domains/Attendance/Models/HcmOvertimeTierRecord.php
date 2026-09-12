<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmOvertimeTierRecord extends Model
{
    use HasUuids;

    protected $table = 'hcm_overtime_tier_records';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'attendance_session_id',
        'timesheet_id',
        'overtime_date',
        'tier_1_minutes',
        'tier_2_minutes',
        'tier_3_minutes',
        'total_overtime_minutes',
        'overtime_category',
        'is_unauthorized',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'tier_1_minutes' => 'integer',
        'tier_2_minutes' => 'integer',
        'tier_3_minutes' => 'integer',
        'total_overtime_minutes' => 'integer',
        'is_unauthorized' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class, 'timesheet_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}