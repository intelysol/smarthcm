<?php

namespace App\Domains\Absence\Services;

use App\Domains\Absence\Models\HcmAbsenceCase;
use App\Domains\Absence\Models\HcmAbsenceEvent;
use App\Domains\Absence\Models\HcmAbsencePeriod;
use App\Domains\Attendance\Models\AttendanceException;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AbsenceOrchestrationService
{
    public function __construct(
        protected AbsenceOperationalImpactService $impactService
    ) {}

    /**
     * Report an absence event (planned or unplanned).
     */
    public function reportAbsence(
        string $tenantId,
        string $employeeId,
        Carbon $absenceDate,
        float $durationHours,
        string $category = 'unplanned_sick',
        string $source = 'employee_self_service',
        ?string $startTime = null,
        ?string $endTime = null,
        ?string $leaveApplicationId = null,
        ?string $attendanceExceptionId = null,
        ?bool $isPlanned = null,
        ?int $createdById = null
    ): HcmAbsenceEvent {
        if ($isPlanned === null) {
            $isPlanned = in_array($category, ['vacation', 'planned_personal', 'parental', 'training', 'scheduled_leave']);
        }

        $event = HcmAbsenceEvent::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'employee_id' => $employeeId,
            'absence_date' => $absenceDate->toDateString(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_hours' => $durationHours,
            'absence_category' => $category,
            'source' => $source,
            'leave_application_id' => $leaveApplicationId,
            'attendance_exception_id' => $attendanceExceptionId,
            'is_planned' => $isPlanned,
            'status' => 'reported',
            'reported_at' => now(),
            'created_by' => $createdById,
        ]);

        // Aggregate into / update continuous Absence Period
        $this->aggregateIntoPeriod($event);

        // Evaluate operational schedule impact
        $this->impactService->evaluateImpact($event);

        return $event;
    }

    /**
     * Convert an approved leave application into an absence event.
     */
    public function recordLeaveAbsence(
        string $tenantId,
        string $employeeId,
        string $leaveApplicationId,
        Carbon $startDate,
        Carbon $endDate,
        float $durationHours,
        string $category = 'vacation'
    ): HcmAbsenceEvent {
        return $this->reportAbsence(
            $tenantId,
            $employeeId,
            $startDate,
            $durationHours,
            $category,
            'leave',
            null,
            null,
            $leaveApplicationId,
            null,
            true
        );
    }

    /**
     * Convert a no-show attendance exception into an unplanned absence event.
     */
    public function recordNoShowAbsence(
        string $tenantId,
        string $employeeId,
        string $attendanceExceptionId,
        Carbon $absenceDate,
        float $durationHours = 8.00
    ): HcmAbsenceEvent {
        return $this->reportAbsence(
            $tenantId,
            $employeeId,
            $absenceDate,
            $durationHours,
            'unplanned_sick',
            'attendance_exception',
            null,
            null,
            null,
            $attendanceExceptionId,
            false
        );
    }

    /**
     * Aggregates single events into continuous absence periods and flags long-term absences.
     */
    public function aggregateIntoPeriod(HcmAbsenceEvent $event, int $longTermThresholdDays = 30): HcmAbsencePeriod
    {
        $tenantId = $event->tenant_id;
        $employeeId = $event->employee_id;
        $date = Carbon::parse($event->absence_date);

        // Find active open period within adjacent days
        $period = HcmAbsencePeriod::where('tenant_id', $tenantId)
            ->where('employee_id', $employeeId)
            ->whereIn('status', ['reported', 'active', 'extended'])
            ->where('end_date', '>=', $date->copy()->subDays(2)->toDateString())
            ->where('start_date', '<=', $date->copy()->addDays(2)->toDateString())
            ->first();

        if ($period) {
            $newStart = Carbon::parse($period->start_date)->min($date);
            $newEnd = Carbon::parse($period->end_date)->max($date);
            $calendarDays = $newStart->diffInDays($newEnd) + 1;
            $workingDays = max(1, (int) round($calendarDays * (5 / 7)));
            $totalHours = (float) $period->total_absence_hours + (float) $event->duration_hours;
            $isLongTerm = $calendarDays >= $longTermThresholdDays;

            $period->update([
                'start_date' => $newStart->toDateString(),
                'end_date' => $newEnd->toDateString(),
                'working_days_count' => $workingDays,
                'calendar_days_count' => $calendarDays,
                'total_absence_hours' => $totalHours,
                'is_long_term' => $isLongTerm,
                'expected_return_date' => $newEnd->copy()->addDay()->toDateString(),
            ]);
        } else {
            $isLongTerm = false;
            $period = HcmAbsencePeriod::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'employee_id' => $employeeId,
                'start_date' => $date->toDateString(),
                'end_date' => $date->toDateString(),
                'working_days_count' => 1,
                'calendar_days_count' => 1,
                'total_absence_hours' => $event->duration_hours,
                'absence_category' => $event->absence_category,
                'is_long_term' => $isLongTerm,
                'status' => 'active',
                'expected_return_date' => $date->copy()->addDay()->toDateString(),
                'leave_application_id' => $event->leave_application_id,
            ]);
        }

        // If newly long-term, create an Absence Case for active HR management
        if ($period->is_long_term && ! HcmAbsenceCase::where('absence_period_id', $period->id)->exists()) {
            HcmAbsenceCase::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'case_number' => 'ABS-CASE-' . strtoupper(Str::random(8)),
                'employee_id' => $employeeId,
                'absence_period_id' => $period->id,
                'case_type' => 'long_term_absence',
                'severity' => 'elevated',
                'status' => 'opened',
                'trigger_reason' => "Absence exceeded {$longTermThresholdDays} calendar days.",
                'opened_at' => now(),
            ]);
        }

        return $period;
    }
}