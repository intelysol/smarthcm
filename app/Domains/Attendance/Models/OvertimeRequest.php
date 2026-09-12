<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRequest extends Model
{
    use HasUuids;

    protected $table = 'overtime_requests';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'attendance_session_id',
        'overtime_date',
        'calculated_overtime_minutes',
        'requested_overtime_minutes',
        'approved_overtime_minutes',
        'overtime_type',
        'reason',
        'status',
        'workflow_instance_id',
        'requested_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'calculated_overtime_minutes' => 'integer',
        'requested_overtime_minutes' => 'integer',
        'approved_overtime_minutes' => 'integer',
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

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
