<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmLaborComplianceCheck extends Model
{
    use HasUuids;

    protected $table = 'hcm_labor_compliance_checks';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'check_date',
        'attendance_session_id',
        'rule_code',
        'severity',
        'threshold_value',
        'actual_value',
        'status',
        'waiver_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'check_date' => 'date',
        'threshold_value' => 'integer',
        'actual_value' => 'integer',
        'reviewed_at' => 'datetime',
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}