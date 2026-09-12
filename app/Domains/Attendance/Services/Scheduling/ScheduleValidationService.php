<?php

namespace App\Domains\Attendance\Services\Scheduling;

use App\Domains\Attendance\Models\HcmScheduleCoverageRequirement;
use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Models\RosterAssignment;
use Carbon\CarbonImmutable;

class ScheduleValidationService
{
    public function __construct(
        protected ScheduleEligibilityService $eligibilityService
    ) {}

    /**
     * Validate an entire roster period.
     *
     * @return array{
     *     validation_status: string,
     *     critical_count: int,
     *     warning_count: int,
     *     hard_violations: array<int, array<string, mixed>>,
     *     soft_warnings: array<int, array<string, mixed>>,
     *     coverage_score: float,
     *     schedule_quality_score: float,
     *     total_assignments: int
     * }
     */
    public function validatePeriod(RosterPeriod $period): array
    {
        $assignments = $period->assignments()->with(['employee', 'shift'])->get();
        $coverageRequirements = $period->coverageRequirements()->get();

        $hardViolations = [];
        $softWarnings = [];

        foreach ($assignments as $assignment) {
            if (! $assignment->employee || ! $assignment->shift) {
                continue;
            }

            $eligibility = $this->eligibilityService->checkEligibility(
                employee: $assignment->employee,
                date: $assignment->roster_date->toDateString(),
                shift: $assignment->shift,
                currentAssignmentId: $assignment->id
            );

            foreach ($eligibility['hard_violations'] as $violation) {
                $hardViolations[] = [
                    'assignment_id' => $assignment->id,
                    'employee_id' => $assignment->employee_id,
                    'employee_name' => $assignment->employee->full_name ?? 'Employee',
                    'date' => $assignment->roster_date->toDateString(),
                    'message' => $violation,
                ];
            }

            foreach ($eligibility['soft_warnings'] as $warning) {
                $softWarnings[] = [
                    'assignment_id' => $assignment->id,
                    'employee_id' => $assignment->employee_id,
                    'employee_name' => $assignment->employee->full_name ?? 'Employee',
                    'date' => $assignment->roster_date->toDateString(),
                    'message' => $warning,
                ];
            }
        }

        // Coverage Assessment
        $totalRequired = $coverageRequirements->sum('required_headcount');
        $coveredCount = 0;

        foreach ($coverageRequirements as $req) {
            $matchingScheduled = $assignments->filter(function ($a) use ($req) {
                return $a->roster_date->toDateString() === $req->requirement_date->toDateString()
                    && $a->shift_definition_id === $req->shift_definition_id;
            })->count();

            $coveredCount += min($matchingScheduled, $req->required_headcount);
        }

        $coverageScore = $totalRequired > 0 ? round(($coveredCount / $totalRequired) * 100, 2) : 100.00;

        // Schedule Quality Score (Technical compliance metric; never employee performance)
        $criticalCount = count($hardViolations);
        $warningCount = count($softWarnings);

        $penalty = ($criticalCount * 25) + ($warningCount * 5);
        $scheduleQualityScore = max(0.00, round(($coverageScore * 0.7) + (max(0, 100 - $penalty) * 0.3), 2));

        $validationStatus = $criticalCount > 0 ? 'critical_violation' : ($warningCount > 0 ? 'warning' : 'valid');

        $summary = [
            'validation_status' => $validationStatus,
            'critical_count' => $criticalCount,
            'warning_count' => $warningCount,
            'hard_violations' => $hardViolations,
            'soft_warnings' => $softWarnings,
            'coverage_score' => $coverageScore,
            'schedule_quality_score' => $scheduleQualityScore,
            'total_assignments' => $assignments->count(),
            'validated_at' => now()->toISOString(),
        ];

        // Update period
        $period->update([
            'validation_status' => $validationStatus,
            'validation_summary' => $summary,
            'schedule_quality_score' => $scheduleQualityScore,
        ]);

        return $summary;
    }
}
