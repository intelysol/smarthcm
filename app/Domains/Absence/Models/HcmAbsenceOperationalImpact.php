<?php

namespace App\Domains\Absence\Models;

use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmAbsenceOperationalImpact extends Model
{
    use HasUuids;

    protected $table = 'hcm_absence_operational_impacts';

    protected $fillable = [
        'tenant_id',
        'absence_event_id',
        'employee_id',
        'impact_date',
        'affected_shift_id',
        'affected_roster_assignment_id',
        'scheduled_hours',
        'lost_capacity_hours',
        'department_id',
        'location_id',
        'coverage_status',
        'replacement_employee_id',
        'replacement_strategy',
    ];

    protected $casts = [
        'impact_date' => 'date',
        'scheduled_hours' => 'decimal:2',
        'lost_capacity_hours' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function absenceEvent(): BelongsTo
    {
        return $this->belongsTo(HcmAbsenceEvent::class, 'absence_event_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function replacementEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'replacement_employee_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftDefinition::class, 'affected_shift_id');
    }

    public function rosterAssignment(): BelongsTo
    {
        return $this->belongsTo(RosterAssignment::class, 'affected_roster_assignment_id');
    }
}