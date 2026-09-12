<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmTimeAllocation extends Model
{
    use HasUuids;

    protected $table = 'hcm_time_allocations';

    protected $fillable = [
        'tenant_id',
        'timesheet_id',
        'timesheet_entry_id',
        'employee_id',
        'allocation_date',
        'project_id',
        'project_code',
        'task_id',
        'task_name',
        'cost_center_id',
        'client_id',
        'work_type',
        'allocated_minutes',
        'is_billable',
        'description',
    ];

    protected $casts = [
        'allocation_date' => 'date',
        'allocated_minutes' => 'integer',
        'is_billable' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class, 'timesheet_id');
    }

    public function timesheetEntry(): BelongsTo
    {
        return $this->belongsTo(TimesheetEntry::class, 'timesheet_entry_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}