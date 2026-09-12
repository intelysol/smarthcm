<?php

namespace App\Domains\Attendance\Services\Scheduling;

use App\Domains\Attendance\Models\HcmEmployeeShiftPreference;
use App\Domains\Attendance\Models\HcmScheduleOptimizationRun;
use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Carbon\CarbonImmutable;

class ScheduleOptimizationService
{
    public function __construct(
        protected ScheduleEligibilityService $eligibilityService,
        protected ScheduleValidationService $validationService,
        protected CoverageCalculationService $coverageService
    ) {}

    /**
     * Run deterministic rule-based schedule optimization.
     */
    public function optimize(
        RosterPeriod $period,
        string $strategy = 'rule_based_heuristic',
        ?User $actor = null
    ): HcmScheduleOptimizationRun {
        $metricsBefore = $this->coverageService->getCoverageMatrix($period);

        $optimizationRun = HcmScheduleOptimizationRun::query()->create([
            'tenant_id' => $period->tenant_id,
            'roster_period_id' => $period->id,
            'strategy' => $strategy,
            'status' => 'running',
            'metrics_before' => $metricsBefore,
            'ran_by' => $actor?->id,
        ]);

        $requirements = $period->coverageRequirements()->with(['shift', 'requiredSkill'])->get();
        $employees = Employee::query()
            ->where('tenant_id', $period->tenant_id)
            ->when($period->department_id, fn ($q) => $q->where('department_id', $period->department_id))
            ->where('employment_status', 'active')
            ->get();

        $proposedAssignments = [];
        $tempWeeklyHours = [];

        foreach ($requirements as $requirement) {
            $dateStr = $requirement->requirement_date->toDateString();
            $shift = $requirement->shift;
            if (! $shift) {
                continue;
            }

            // Count existing assignments
            $existingCount = RosterAssignment::query()
                ->where('tenant_id', $period->tenant_id)
                ->where('roster_period_id', $period->id)
                ->whereDate('roster_date', $dateStr)
                ->where('shift_definition_id', $shift->id)
                ->where('assignment_status', 'scheduled')
                ->count();

            // Also count already proposed in this run
            $runCount = collect($proposedAssignments)
                ->where('date', $dateStr)
                ->where('shift_id', $shift->id)
                ->count();

            $needed = $requirement->required_headcount - ($existingCount + $runCount);
            if ($needed <= 0) {
                continue;
            }

            // Score and rank eligible candidates
            $scoredCandidates = [];
            foreach ($employees as $emp) {
                // Check if already assigned on this day
                $alreadyAssigned = RosterAssignment::query()
                    ->where('tenant_id', $period->tenant_id)
                    ->where('employee_id', $emp->id)
                    ->whereDate('roster_date', $dateStr)
                    ->where('assignment_status', 'scheduled')
                    ->exists()
                    || collect($proposedAssignments)->where('employee_id', $emp->id)->where('date', $dateStr)->isNotEmpty();

                if ($alreadyAssigned) {
                    continue;
                }

                $eligibility = $this->eligibilityService->checkEligibility(
                    employee: $emp,
                    date: $dateStr,
                    shift: $shift,
                    requiredSkillId: $requirement->required_skill_id,
                    minSkillProficiency: $requirement->min_proficiency_level
                );

                if (! $eligibility['is_eligible']) {
                    continue;
                }

                // Preference evaluation
                $carbonDate = CarbonImmutable::parse($dateStr);
                $pref = HcmEmployeeShiftPreference::query()
                    ->where('tenant_id', $period->tenant_id)
                    ->where('employee_id', $emp->id)
                    ->where(function ($q) use ($shift, $carbonDate) {
                        $q->where('shift_definition_id', $shift->id)
                            ->orWhere('day_of_week', $carbonDate->dayOfWeek);
                    })
                    ->first();

                $prefScore = 0;
                if ($pref) {
                    $prefScore = $pref->preference_type === 'preferred' ? 20 : -30;
                }

                // Balance workload: lower existing hours gets higher priority
                $empCurrentHours = $tempWeeklyHours[$emp->id] ?? 0;
                $workloadScore = max(0, 50 - ($empCurrentHours * 2));

                $totalScore = 50 + $prefScore + $workloadScore;

                $scoredCandidates[] = [
                    'employee' => $emp,
                    'score' => $totalScore,
                    'eligibility' => $eligibility,
                ];
            }

            // Sort by highest score
            usort($scoredCandidates, fn ($a, $b) => $b['score'] <=> $a['score']);

            // Pick top candidates
            $selected = array_slice($scoredCandidates, 0, $needed);
            foreach ($selected as $item) {
                /** @var Employee $selectedEmp */
                $selectedEmp = $item['employee'];
                $proposedAssignments[] = [
                    'employee_id' => $selectedEmp->id,
                    'employee_name' => $selectedEmp->full_name ?? 'Employee',
                    'date' => $dateStr,
                    'shift_id' => $shift->id,
                    'shift_name' => $shift->name,
                    'optimization_score' => $item['score'],
                    'rationale' => "Satisfies coverage requirement ({$requirement->required_headcount}) with zero hard violations.",
                ];

                $tempWeeklyHours[$selectedEmp->id] = ($tempWeeklyHours[$selectedEmp->id] ?? 0) + ($shift->duration_minutes / 60);
            }
        }

        // Metrics after proposal
        $metricsAfter = $metricsBefore;
        $metricsAfter['proposed_assignment_count'] = count($proposedAssignments);
        $newScheduled = $metricsBefore['overall_scheduled'] + count($proposedAssignments);
        $metricsAfter['projected_scheduled'] = $newScheduled;
        $metricsAfter['projected_gap'] = $newScheduled - $metricsBefore['overall_required'];
        $metricsAfter['projected_coverage_pct'] = $metricsBefore['overall_required'] > 0
            ? round(($newScheduled / $metricsBefore['overall_required']) * 100, 1)
            : 100.0;

        $qualityScore = min(100.0, max(0.0, (float) $metricsAfter['projected_coverage_pct']));

        $optimizationRun->update([
            'status' => 'completed',
            'metrics_after' => $metricsAfter,
            'proposed_assignments' => $proposedAssignments,
            'quality_score' => $qualityScore,
        ]);

        return $optimizationRun;
    }

    /**
     * Apply proposed assignments into active roster assignments upon manager approval.
     */
    public function applyProposedAssignments(
        HcmScheduleOptimizationRun $run,
        User $manager
    ): int {
        $proposals = $run->proposed_assignments ?? [];
        $appliedCount = 0;

        foreach ($proposals as $item) {
            RosterAssignment::query()->updateOrCreate(
                [
                    'tenant_id' => $run->tenant_id,
                    'employee_id' => $item['employee_id'],
                    'roster_date' => $item['date'],
                ],
                [
                    'roster_period_id' => $run->roster_period_id,
                    'shift_definition_id' => $item['shift_id'],
                    'assignment_status' => 'scheduled',
                    'is_published' => false,
                    'notes' => 'Generated by Optimization Run: ' . $run->id,
                    'created_by' => $manager->id,
                    'updated_by' => $manager->id,
                ]
            );
            $appliedCount++;
        }

        $period = $run->rosterPeriod;
        if ($period) {
            $this->validationService->validatePeriod($period);
        }

        return $appliedCount;
    }
}
