<?php

namespace App\Domains\Attendance\Services\Scheduling;

use App\Domains\Attendance\Models\HcmScheduleCoverageRequirement;
use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\ShiftDefinition;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class CoverageCalculationService
{
    /**
     * Compute coverage matrix and heatmap by date and shift.
     *
     * @return array{
     *     overall_required: int,
     *     overall_scheduled: int,
     *     overall_gap: int,
     *     overall_coverage_pct: float,
     *     matrix: array<string, mixed>,
     *     heatmap: array<int, array<string, mixed>>
     * }
     */
    public function getCoverageMatrix(RosterPeriod $period): array
    {
        $requirements = $period->coverageRequirements()->with(['shift', 'department', 'requiredSkill'])->get();
        $assignments = $period->assignments()->with('shift')->where('assignment_status', 'scheduled')->get();

        $carbonStart = CarbonImmutable::parse($period->start_date);
        $carbonEnd = CarbonImmutable::parse($period->end_date);
        $dates = CarbonPeriod::create($carbonStart, $carbonEnd);

        $matrix = [];
        $heatmap = [];
        $overallRequired = 0;
        $overallScheduled = 0;

        foreach ($dates as $date) {
            $dateStr = $date->format('Y-m-d');
            $dayRequirements = $requirements->filter(function ($r) use ($dateStr) {
                return $r->requirement_date instanceof \DateTimeInterface
                    ? $r->requirement_date->format('Y-m-d') === $dateStr
                    : (string) $r->requirement_date === $dateStr;
            });

            $dayAssignments = $assignments->filter(function ($a) use ($dateStr) {
                return $a->roster_date instanceof \DateTimeInterface
                    ? $a->roster_date->format('Y-m-d') === $dateStr
                    : (string) $a->roster_date === $dateStr;
            });

            // Distinct shifts for this day
            $shiftIds = $dayRequirements->pluck('shift_definition_id')->filter()->unique();
            if ($shiftIds->isEmpty()) {
                $shiftIds = $dayAssignments->pluck('shift_definition_id')->filter()->unique();
            }

            foreach ($shiftIds as $shiftId) {
                $shift = ShiftDefinition::query()->find($shiftId);
                $shiftReq = (int) $dayRequirements->where('shift_definition_id', $shiftId)->sum('required_headcount');
                $shiftScheduled = (int) $dayAssignments->where('shift_definition_id', $shiftId)->count();
                $gap = $shiftScheduled - $shiftReq;
                $pct = $shiftReq > 0 ? round(($shiftScheduled / $shiftReq) * 100, 1) : 100.0;

                $overallRequired += $shiftReq;
                $overallScheduled += $shiftScheduled;

                $row = [
                    'date' => $dateStr,
                    'day_name' => $date->format('l'),
                    'shift_id' => $shiftId,
                    'shift_name' => $shift?->name ?? 'Standard Shift',
                    'required' => $shiftReq,
                    'scheduled' => $shiftScheduled,
                    'gap' => $gap,
                    'coverage_pct' => $pct,
                    'status' => $gap < 0 ? 'under_covered' : ($gap > 0 ? 'over_covered' : 'optimal'),
                ];

                $matrix[$dateStr][$shiftId] = $row;
                $heatmap[] = $row;
            }
        }

        $overallGap = $overallScheduled - $overallRequired;
        $overallPct = $overallRequired > 0 ? round(($overallScheduled / $overallRequired) * 100, 1) : 100.0;

        return [
            'overall_required' => $overallRequired,
            'overall_scheduled' => $overallScheduled,
            'overall_gap' => $overallGap,
            'overall_coverage_pct' => $overallPct,
            'matrix' => $matrix,
            'heatmap' => $heatmap,
        ];
    }

    /**
     * Ingest demand/capacity requirements from Epic 2.45 capacity calculation.
     *
     * @param array<int, array{
     *     date: string,
     *     shift_definition_id: string,
     *     required_headcount: int,
     *     department_id?: string|null,
     *     location_id?: string|null,
     *     required_skill_id?: string|null,
     *     min_proficiency_level?: int|null
     * }> $requirementRows
     */
    public function ingestRequirements(RosterPeriod $period, array $requirementRows): Collection
    {
        $created = collect();

        foreach ($requirementRows as $row) {
            $record = HcmScheduleCoverageRequirement::query()->updateOrCreate(
                [
                    'tenant_id' => $period->tenant_id,
                    'roster_period_id' => $period->id,
                    'requirement_date' => $row['date'],
                    'shift_definition_id' => $row['shift_definition_id'],
                ],
                [
                    'required_headcount' => $row['required_headcount'],
                    'department_id' => $row['department_id'] ?? $period->department_id,
                    'location_id' => $row['location_id'] ?? null,
                    'required_skill_id' => $row['required_skill_id'] ?? null,
                    'min_proficiency_level' => $row['min_proficiency_level'] ?? 1,
                    'capacity_calculation_id' => $row['capacity_calculation_id'] ?? null,
                ]
            );

            $created->push($record);
        }

        return $created;
    }
}
