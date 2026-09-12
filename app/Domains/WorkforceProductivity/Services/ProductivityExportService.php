<?php

namespace App\Domains\WorkforceProductivity\Services;

use App\Domains\WorkforceProductivity\Models\HcmProductivityMeasurement;
use App\Domains\WorkforceProductivity\Models\HcmProductivityScorecard;

class ProductivityExportService
{
    /**
     * Export measurements to CSV formatted string.
     */
    public function exportMeasurementsCsv(string $tenantId, string $startDate, string $endDate): string
    {
        $measurements = HcmProductivityMeasurement::with(['metricDefinition', 'department'])
            ->where('tenant_id', $tenantId)
            ->where('period_start', '>=', $startDate)
            ->where('period_end', '<=', $endDate)
            ->get();

        $rows = [];
        $rows[] = implode(',', [
            'Period',
            'Department',
            'Metric',
            'Output Volume',
            'Productive Hours',
            'Productivity Rate',
            'Utilization Rate (%)',
            'Labor Cost',
            'Cost Per Unit',
            'Quality Status',
        ]);

        foreach ($measurements as $m) {
            $rows[] = implode(',', [
                '"' . $m->period_name . '"',
                '"' . ($m->department ? $m->department->department_name : 'All') . '"',
                '"' . ($m->metricDefinition ? $m->metricDefinition->name : 'N/A') . '"',
                $m->output_volume,
                $m->productive_hours,
                $m->productivity_rate ?? 'N/A',
                $m->utilization_rate ?? 'N/A',
                $m->labor_cost,
                $m->cost_per_unit ?? 'N/A',
                $m->data_quality_status,
            ]);
        }

        return implode("\n", $rows);
    }

    /**
     * Export scorecards to normalized array for Excel or PDF rendering.
     */
    public function exportScorecardsArray(string $tenantId): array
    {
        return HcmProductivityScorecard::where('tenant_id', $tenantId)
            ->latest('period_end')
            ->get()
            ->map(function ($s) {
                return [
                    'entity' => $s->entity_name,
                    'level' => $s->scorecard_level,
                    'period' => "{$s->period_start} to {$s->period_end}",
                    'productivity_score' => $s->productivity_score,
                    'utilization_rate' => $s->utilization_rate,
                    'quality_rate' => $s->quality_rate,
                    'overtime_ratio' => $s->overtime_ratio,
                    'cost_per_unit' => $s->cost_per_unit,
                    'status' => $s->status_label,
                ];
            })
            ->toArray();
    }
}
