<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceAdjustment extends Model
{
    use HasUuids;

    protected $table = 'attendance_adjustments';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'attendance_session_id',
        'adjustment_date',
        'adjustment_type',
        'original_values',
        'requested_values',
        'reason',
        'supporting_document_id',
        'status',
        'workflow_instance_id',
        'requested_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'original_values' => 'array',
        'requested_values' => 'array',
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

    public function audits(): HasMany
    {
        return $this->hasMany(HcmAttendanceCorrectionAudit::class, 'attendance_adjustment_id');
    }
}
