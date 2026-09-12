<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\WorkLocation;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RosterAssignment extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'roster_assignments';

    protected $fillable = [
        'tenant_id',
        'roster_period_id',
        'employee_id',
        'roster_date',
        'shift_definition_id',
        'location_id',
        'department_id',
        'assignment_status',
        'is_published',
        'replacement_employee_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'roster_date' => 'date',
        'is_published' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function rosterPeriod(): BelongsTo
    {
        return $this->belongsTo(RosterPeriod::class, 'roster_period_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftDefinition::class, 'shift_definition_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class, 'location_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function replacementEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'replacement_employee_id');
    }

    public function conflicts(): HasMany
    {
        return $this->hasMany(RosterConflict::class);
    }

    public function changeLogs(): HasMany
    {
        return $this->hasMany(HcmScheduleChangeLog::class, 'roster_assignment_id');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(HcmScheduleException::class, 'roster_assignment_id');
    }

    public function swapRequests(): HasMany
    {
        return $this->hasMany(HcmShiftSwapRequest::class, 'requesting_assignment_id');
    }
}
