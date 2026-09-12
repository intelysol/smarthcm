<?php

namespace App\Domains\Analytics\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class HcmAiAnalyticsService
{
    public function __construct(
        protected HcmMetricRegistryService $metricRegistry,
        protected HcmWorkforceAnalyticsService $workforceService,
        protected HcmTimeAndAttendanceAnalyticsService $attendanceService
    ) {}

    public function processNaturalLanguageQuery(string $tenantId, string $question, User $user): array
    {
        $normalized = strtolower($question);

        // 1. Intent & Metric Resolution
        if (str_contains($normalized, 'turnover') || str_contains($normalized, 'attrition')) {
            $metric = 'HCM_TURNOVER_RATE';
            $data = $this->workforceService->getTurnoverAnalytics($tenantId, now()->startOfYear()->toDateString(), now()->toDateString());
            $explanation = "The annualized turnover rate is currently {$data['turnover_rate_percent']}%, with {$data['voluntary_exits']} voluntary exits and {$data['involuntary_exits']} involuntary exits recorded across {$data['average_headcount']} average active headcount.";
        } elseif (str_contains($normalized, 'headcount') || str_contains($normalized, 'how many') || str_contains($normalized, 'active employees')) {
            $metric = 'HCM_HEADCOUNT_ACTIVE';
            $data = $this->workforceService->getHeadcountSummary($tenantId);
            $explanation = "Total headcount stands at {$data['total_headcount']} ({$data['active_headcount']} active, {$data['fte_total']} FTE). {$data['full_time']} employees are full-time and {$data['part_time']} are part-time.";
        } elseif (str_contains($normalized, 'attendance') || str_contains($normalized, 'absent') || str_contains($normalized, 'late')) {
            $metric = 'HCM_ATTENDANCE_RATE';
            $data = $this->attendanceService->getAttendanceMetrics($tenantId, now()->startOfMonth()->toDateString(), now()->toDateString());
            $explanation = "The average attendance rate for the current month is {$data['attendance_rate_percent']}%, with an absenteeism rate of {$data['absenteeism_rate_percent']}%. Total overtime logged is {$data['total_overtime_hours']} hours.";
        } else {
            $metric = 'HCM_HEADCOUNT_ACTIVE';
            $data = $this->workforceService->getHeadcountSummary($tenantId);
            $explanation = "General workforce metrics: active headcount is {$data['active_headcount']} across " . count($data['by_department']) . " departments.";
        }

        return [
            'query' => $question,
            'resolved_metric_code' => $metric,
            'underlying_data' => $data,
            'ai_explanation' => $explanation,
            'is_advisory_only' => true,
            'data_sources_cited' => ['Core HR Employee Registry', 'Workforce Snapshots', 'Time & Attendance Engine'],
        ];
    }
}
