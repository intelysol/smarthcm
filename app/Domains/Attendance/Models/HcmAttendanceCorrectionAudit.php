<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmAttendanceCorrectionAudit extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'hcm_attendance_correction_audits';

    protected $fillable = [
        'tenant_id',
        'attendance_adjustment_id',
        'employee_id',
        'action',
        'actor_id',
        'before_snapshot',
        'after_snapshot',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'before_snapshot' => 'array',
        'after_snapshot' => 'array',
        'created_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(AttendanceAdjustment::class, 'attendance_adjustment_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}