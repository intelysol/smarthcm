<?php

namespace App\Domains\WorkforceProductivity\Services;

use App\Domains\WorkforceProductivity\Models\HcmProductivityAnomaly;
use App\Domains\WorkforceProductivity\Models\HcmProductivityMeasurement;

class AdvisoryWorkforceProductivityAiService
{
    public const IS_ADVISORY_ONLY = true;
    public const AUTONOMOUS_ACTIONS_PERMITTED = false;

    /**
     * Generate advisory productivity insights with strict non-punitive and ethical guardrails.
     */
    public function generateInsights(string $tenantId, ?string $departmentId = null): array
    {
        $query = HcmProductivityMeasurement::where('tenant_id', $tenantId);
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        $recent = $query->latest('period_end')->take(6)->get();

        $insights = [];

        // 1. Trend Analysis
        if ($recent->count() >= 2) {
            $current = $recent[0];
            $previous = $recent[1];

            $currRate = (float) $current->productivity_rate;
            $prevRate = (float) $previous->productivity_rate;

            if ($prevRate > 0) {
                $pctChange = round((($currRate - $prevRate) / $prevRate) * 100.0, 2);
                $observed = $pctChange >= 0
                    ? "Productivity increased by {$pctChange}% compared to previous period."
                    : "Productivity declined by " . abs($pctChange) . "% compared to previous period.";

                $insights[] = [
                    'insight_type' => 'trend_analysis',
                    'metric' => 'Productivity Rate',
                    'period' => $current->period_name,
                    'data_sources' => ['hcm_productivity_measurements'],
                    'observed_change' => $observed,
                    'possible_drivers' => $pctChange < 0
                        ? ['Shift schedule coverage mismatch', 'Waiting time during process handoffs', 'Absence cluster in peak operational windows']
                        : ['Improved schedule adherence', 'Higher volume throughput with stabilized productive hours'],
                    'confidence' => 0.88,
                    'classification' => 'OBSERVED',
                ];
            }

            // 2. Cost-Productivity Divergence Check
            $currCost = (float) $current->labor_cost;
            $prevCost = (float) $previous->labor_cost;
            if ($prevCost > 0 && $prevRate > 0) {
                $costChange = round((($currCost - $prevCost) / $prevCost) * 100.0, 2);
                if (isset($pctChange) && $costChange > $pctChange + 5.0) {
                    $insights[] = [
                        'insight_type' => 'cost_productivity_divergence',
                        'metric' => 'Labor Cost vs Output',
                        'period' => $current->period_name,
                        'data_sources' => ['hcm_productivity_measurements', 'hcm_workforce_cost_lines'],
                        'observed_change' => "Labor cost increased by {$costChange}% while productivity changed by {$pctChange}%.",
                        'possible_drivers' => [
                            'Overtime premium dilution',
                            'Unplanned absence requiring premium contractor substitution',
                            'Shift differential or wage rate adjustments',
                        ],
                        'confidence' => 0.92,
                        'classification' => 'INFERRED',
                    ];
                }
            }
        }

        // 3. Bottleneck Analysis from logged anomalies
        $anomalies = HcmProductivityAnomaly::where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->latest('detected_date')
            ->take(3)
            ->get();

        foreach ($anomalies as $anomaly) {
            $insights[] = [
                'insight_type' => 'bottleneck_alert',
                'metric' => $anomaly->anomaly_type,
                'period' => (string) $anomaly->detected_date,
                'data_sources' => ['hcm_productivity_anomalies', 'hcm_productivity_time_records'],
                'observed_change' => "Anomaly ({$anomaly->severity}) detected with variance of {$anomaly->variance_pct}%.",
                'possible_drivers' => $anomaly->possible_drivers ?? ['Process bottleneck or waiting time concentration'],
                'confidence' => 0.85,
                'classification' => 'RECOMMENDED',
            ];
        }

        return [
            'governance' => [
                'is_advisory_only' => self::IS_ADVISORY_ONLY,
                'autonomous_actions_permitted' => self::AUTONOMOUS_ACTIONS_PERMITTED,
                'surveillance_telemetry_prohibited' => true,
                'disciplinary_action_prohibited' => true,
            ],
            'insights' => $insights,
        ];
    }
}
