<?php

namespace App\Domains\WorkforceProductivity\Http\Controllers;

use App\Domains\WorkforceProductivity\Contracts\ProductivityCalculationInterface;
use App\Domains\WorkforceProductivity\Models\HcmProductivityOutputRecord;
use App\Domains\WorkforceProductivity\Models\HcmProductivityTimeRecord;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductivityImpactController extends Controller
{
    public function __construct(
        protected ProductivityCalculationInterface $calculator
    ) {}

    /**
     * Analyze Overtime vs Regular Productivity Differential.
     */
    public function overtime(Request $request): JsonResponse
    {
        $regularOutput = (float) ($request->query('regular_output') ?? 1000.0);
        $regularHours = (float) ($request->query('regular_hours') ?? 100.0);
        $overtimeOutput = (float) ($request->query('overtime_output') ?? 140.0);
        $overtimeHours = (float) ($request->query('overtime_hours') ?? 20.0);

        $regularRate = $this->calculator->calculateRate($regularOutput, $regularHours);
        $overtimeRate = $this->calculator->calculateRate($overtimeOutput, $overtimeHours);

        $differentialPct = ($regularRate > 0 && $overtimeRate !== null)
            ? round((($overtimeRate - $regularRate) / $regularRate) * 100.0, 2)
            : 0.0;

        return response()->json([
            'regular_productivity_rate' => $regularRate,
            'overtime_productivity_rate' => $overtimeRate,
            'productivity_differential_pct' => $differentialPct,
            'diminishing_returns_detected' => $differentialPct < 0,
            'advisory_note' => $differentialPct < 0
                ? 'Overtime productivity is lower than regular productivity; consider fatigue and scheduling adjustments.'
                : 'Overtime output rate is consistent with regular hours.',
        ]);
    }

    /**
     * Analyze Absence Operational and Productivity Drag.
     */
    public function absenceImpact(Request $request): JsonResponse
    {
        $lostHours = (float) ($request->query('lost_hours') ?? 160.0);
        $replacementHours = (float) ($request->query('replacement_hours') ?? 120.0);
        $standardRate = (float) ($request->query('standard_rate') ?? 10.0); // units per hour
        $replacementEfficiencyPct = (float) ($request->query('replacement_efficiency_pct') ?? 75.0);

        $lostDirectOutput = round($lostHours * $standardRate, 4);
        $replacementOutput = round($replacementHours * $standardRate * ($replacementEfficiencyPct / 100.0), 4);
        $netOutputDrag = max(0.0, round($lostDirectOutput - $replacementOutput, 4));

        return response()->json([
            'lost_capacity_hours' => $lostHours,
            'replacement_hours' => $replacementHours,
            'lost_direct_output' => $lostDirectOutput,
            'replacement_output' => $replacementOutput,
            'net_output_drag' => $netOutputDrag,
            'capacity_gap_hours' => max(0.0, $lostHours - $replacementHours),
            'label' => 'ESTIMATED',
        ]);
    }

    /**
     * Analyze Employee Turnover & Ramp-Up Productivity Drag.
     */
    public function turnoverImpact(Request $request): JsonResponse
    {
        $vacantDays = (int) ($request->query('vacant_days') ?? 30);
        $rampUpDays = (int) ($request->query('ramp_up_days') ?? 60);
        $dailyStandardOutput = (float) ($request->query('daily_standard_output') ?? 50.0);
        $avgRampEfficiencyPct = (float) ($request->query('ramp_efficiency_pct') ?? 60.0);

        $vacancyOutputLoss = round($vacantDays * $dailyStandardOutput, 4);
        $rampUpLoss = round($rampUpDays * $dailyStandardOutput * ((100.0 - $avgRampEfficiencyPct) / 100.0), 4);
        $totalTurnoverOutputDrag = round($vacancyOutputLoss + $rampUpLoss, 4);

        return response()->json([
            'vacant_days' => $vacantDays,
            'ramp_up_days' => $rampUpDays,
            'vacancy_output_loss' => $vacancyOutputLoss,
            'ramp_up_output_loss' => $rampUpLoss,
            'total_turnover_output_drag' => $totalTurnoverOutputDrag,
            'label' => 'ESTIMATED_MODEL',
        ]);
    }
}
