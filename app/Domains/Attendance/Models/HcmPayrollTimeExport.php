<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmPayrollTimeExport extends Model
{
    use HasUuids;

    protected $table = 'hcm_payroll_time_exports';

    protected $fillable = [
        'tenant_id',
        'export_reference',
        'attendance_period_id',
        'period_start',
        'period_end',
        'total_employees',
        'total_regular_hours',
        'total_overtime_hours',
        'total_leave_hours',
        'export_payload',
        'status',
        'exported_by',
        'exported_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_employees' => 'integer',
        'total_regular_hours' => 'decimal:2',
        'total_overtime_hours' => 'decimal:2',
        'total_leave_hours' => 'decimal:2',
        'export_payload' => 'array',
        'exported_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(AttendancePeriod::class, 'attendance_period_id');
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(HcmPayrollTimeReconciliation::class, 'payroll_time_export_id');
    }

    public function exporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exported_by');
    }
}