<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Enums\ConflictSeverity;
use App\Domains\Attendance\Enums\ConflictType;
use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\RosterConflict;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Employee\Models\Employee;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class RosterConflictEngine
{
    /**
     * Inspect a planned roster assignment for potential scheduling conflicts.
     *
     * @return array<int, array{
     *     type: string,
     *     severity: string,
     *     message: string
     * }>
     */
    public function detectConflicts(Employee $employee, string $date, ?ShiftDefinition $shift = null, ?string $currentAssignmentId = null): array
    {
        $conflicts = [];
        $carbonDate = CarbonImmutable::parse($date);
        $dateStr = $carbonDate->toDateString();
        $tenantId = $employee->tenant_id;

        // 1. Inactive Employment Check
        if ($employee->employment_status && in_array(strtolower($employee->employment_status), ['terminated', 'resigned', 'suspended', 'inactive'], true)) {
            $conflicts[] = [
                'type' => ConflictType::INACTIVE_EMPLOYMENT->value,
                'severity' => ConflictSeverity::BLOCKING->value,
                'message' => "Employee is in inactive status ({$employee->employment_status}) on {$dateStr}.",
            ];
        }

        // 2. Approved Leave Conflict Check
        $hasApprovedLeave = false;
        $leaveTable = \Illuminate\Support\Facades\Schema::hasTable('leave_applications') ? 'leave_applications' : (\Illuminate\Support\Facades\Schema::hasTable('leave_requests') ? 'leave_requests' : null);
        if ($leaveTable) {
            $hasApprovedLeave = DB::table($leaveTable)
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where('start_date', '<=', $dateStr)
                ->where('end_date', '>=', $dateStr)
                ->exists();
        }

        if ($hasApprovedLeave) {
            $conflicts[] = [
                'type' => ConflictType::LEAVE_CONFLICT->value,
                'severity' => ConflictSeverity::BLOCKING->value,
                'message' => "Employee has approved leave scheduled on {$dateStr}.",
            ];
        }

        // If no shift is being assigned (e.g. off-day), further shift checks not needed
        if (! $shift) {
            return $conflicts;
        }

        // 3. Duplicate / Overlapping Assignment Check
        $otherAssignment = RosterAssignment::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->whereDate('roster_date', $dateStr)
            ->when($currentAssignmentId, fn ($q) => $q->where('id', '!=', $currentAssignmentId))
            ->where('assignment_status', '!=', 'cancelled')
            ->first();

        if ($otherAssignment) {
            $conflicts[] = [
                'type' => ConflictType::DUPLICATE_ASSIGNMENT->value,
                'severity' => ConflictSeverity::BLOCKING->value,
                'message' => "Employee is already assigned to a shift on {$dateStr}.",
            ];
        }

        // 4. Insufficient Rest Period Check (< 11 hours = 660 mins between consecutive shifts)
        $prevDate = $carbonDate->subDay()->toDateString();
        $prevAssignment = RosterAssignment::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->whereDate('roster_date', $prevDate)
            ->with('shift')
            ->first();

        if ($prevAssignment && $prevAssignment->shift) {
            $prevEnd = CarbonImmutable::parse("{$prevDate} {$prevAssignment->shift->end_time}");
            if ($prevAssignment->shift->is_night_shift || $prevAssignment->shift->end_time < $prevAssignment->shift->start_time) {
                $prevEnd = $prevEnd->addDay();
            }

            $currentStart = CarbonImmutable::parse("{$dateStr} {$shift->start_time}");
            $restMinutes = $prevEnd->diffInMinutes($currentStart, false);

            if ($restMinutes >= 0 && $restMinutes < 660) {
                $restHours = round($restMinutes / 60, 1);
                $conflicts[] = [
                    'type' => ConflictType::INSUFFICIENT_REST_PERIOD->value,
                    'severity' => ConflictSeverity::WARNING->value,
                    'message' => "Insufficient rest period: Only {$restHours}h rest between previous shift and {$shift->name} (minimum standard is 11h).",
                ];
            }
        }

        // 5. Weekly Excessive Working Hours Check (> 48h in weekly window)
        $startOfWeek = $carbonDate->startOfWeek()->toDateString();
        $endOfWeek = $carbonDate->endOfWeek()->toDateString();

        $assignedMinutes = RosterAssignment::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->whereDate('roster_date', '>=', $startOfWeek)
            ->whereDate('roster_date', '<=', $endOfWeek)
            ->when($currentAssignmentId, fn ($q) => $q->where('id', '!=', $currentAssignmentId))
            ->with('shift')
            ->get()
            ->sum(fn ($a) => $a->shift?->duration_minutes ?? 0);

        $totalProjectedMinutes = $assignedMinutes + $shift->duration_minutes;
        if ($totalProjectedMinutes > 2880) { // 48h * 60m
            $totalHours = round($totalProjectedMinutes / 60, 1);
            $conflicts[] = [
                'type' => ConflictType::EXCESSIVE_HOURS->value,
                'severity' => ConflictSeverity::WARNING->value,
                'message' => "Excessive hours alert: Projected weekly working time is {$totalHours}h (exceeds 48h standard weekly limit).",
            ];
        }

        return $conflicts;
    }

    public function recordConflicts(RosterAssignment $assignment, array $conflicts): void
    {
        $assignment->conflicts()->delete();

        foreach ($conflicts as $conflict) {
            RosterConflict::query()->create([
                'tenant_id' => $assignment->tenant_id,
                'roster_assignment_id' => $assignment->id,
                'employee_id' => $assignment->employee_id,
                'conflict_date' => $assignment->roster_date,
                'conflict_type' => $conflict['type'],
                'severity' => $conflict['severity'],
                'message' => $conflict['message'],
            ]);
        }
    }
}
