<?php

namespace App\Domains\Absence\Services;

use App\Domains\Absence\Models\HcmAbsenceEvent;
use App\Domains\Absence\Models\HcmAbsenceOperationalImpact;
use App\Domains\Attendance\Models\RosterAssignment;
use Illuminate\Support\Str;

class AbsenceOperationalImpactService
{
    /**
     * Evaluate shift and capacity impact of an absence event.
     */
    public function evaluateImpact(HcmAbsenceEvent $event): ?HcmAbsenceOperationalImpact
    {
        $rosterAssignment = RosterAssignment::where('tenant_id', $event->tenant_id)
            ->where('employee_id', $event->employee_id)
            ->whereDate('roster_date', $event->absence_date)
            ->first();

        $shift = $rosterAssignment?->shift;
        $scheduledHours = $shift ? round(($shift->duration_minutes ?? 480) / 60, 2) : (float) $event->duration_hours;
        $lostHours = min($scheduledHours, (float) $event->duration_hours);

        return HcmAbsenceOperationalImpact::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $event->tenant_id,
            'absence_event_id' => $event->id,
            'employee_id' => $event->employee_id,
            'impact_date' => $event->absence_date,
            'affected_shift_id' => $shift?->id,
            'affected_roster_assignment_id' => $rosterAssignment?->id,
            'scheduled_hours' => $scheduledHours,
            'lost_capacity_hours' => $lostHours,
            'department_id' => $event->employee?->department_id,
            'location_id' => $event->employee?->work_location_id,
            'coverage_status' => 'uncovered',
        ]);
    }

    /**
     * Assign replacement employee and set coverage strategy.
     */
    public function assignReplacement(
        string $impactId,
        string $replacementEmployeeId,
        string $strategy = 'internal_allocation'
    ): HcmAbsenceOperationalImpact {
        $impact = HcmAbsenceOperationalImpact::findOrFail($impactId);

        $impact->update([
            'replacement_employee_id' => $replacementEmployeeId,
            'replacement_strategy' => $strategy,
            'coverage_status' => 'covered',
        ]);

        return $impact;
    }
}