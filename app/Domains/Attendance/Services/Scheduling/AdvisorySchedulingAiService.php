<?php

namespace App\Domains\Attendance\Services\Scheduling;

use App\Domains\Attendance\Models\RosterPeriod;

class AdvisorySchedulingAiService
{
    public function __construct(
        protected CoverageCalculationService $coverageService,
        protected ScheduleValidationService $validationService
    ) {}

    /**
     * Provide explainable advisory suggestions for a schedule period.
     *
     * @return array{
     *     is_advisory_only: bool,
     *     human_review_required: bool,
     *     summary: string,
     *     recommendations: array<int, array<string, mixed>>,
     *     coverage_insights: array<string, mixed>
     * }
     */
    public function generateAdvisoryInsights(RosterPeriod $period): array
    {
        $coverage = $this->coverageService->getCoverageMatrix($period);
        $validation = $this->validationService->validatePeriod($period);

        $recommendations = [];

        // Under-coverage recommendations
        $underCoveredShifts = collect($coverage['heatmap'])->where('gap', '<', 0);
        foreach ($underCoveredShifts as $item) {
            $recommendations[] = [
                'type' => 'coverage_shortage',
                'title' => "Staffing deficit on {$item['date']} ({$item['shift_name']})",
                'recommendation' => "Post an Open Shift or request voluntary overtime for {$item['shift_name']}.",
                'reason' => "Required headcount is {$item['required']} but only {$item['scheduled']} scheduled (Deficit: " . abs($item['gap']) . ").",
                'confidence' => 0.92,
                'trade_offs' => 'Potential overtime cost if assigned to existing staff.',
                'is_advisory_only' => true,
            ];
        }

        // Overtime warning recommendations
        if ($validation['warning_count'] > 0) {
            $recommendations[] = [
                'type' => 'overtime_risk_mitigation',
                'title' => 'Excessive hours risk detected',
                'recommendation' => 'Rebalance shifts across part-time or under-utilized staff to reduce weekly fatigue.',
                'reason' => "Detected {$validation['warning_count']} warning(s) relating to weekly hour thresholds or preferences.",
                'confidence' => 0.88,
                'trade_offs' => 'May require minor adjustments to preferred off-days.',
                'is_advisory_only' => true,
            ];
        }

        return [
            'is_advisory_only' => true,
            'human_review_required' => true,
            'summary' => "Schedule period {$period->name} achieves {$coverage['overall_coverage_pct']}% coverage with technical quality score {$validation['schedule_quality_score']}%.",
            'recommendations' => $recommendations,
            'coverage_insights' => [
                'overall_coverage_pct' => $coverage['overall_coverage_pct'],
                'critical_violations' => $validation['critical_count'],
                'warnings' => $validation['warning_count'],
            ],
        ];
    }
}
