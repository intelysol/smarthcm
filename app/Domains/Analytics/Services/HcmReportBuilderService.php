<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HcmReportBuilderService
{
    public function executeReportQuery(string $tenantId, string $dataset, array $config, ?User $actor = null): array
    {
        $dimensions = $config['dimensions'] ?? ['department', 'branch'];
        $metrics = $config['metrics'] ?? ['headcount', 'fte'];
        $filters = $config['filters'] ?? [];
        $grouping = $config['grouping'] ?? $dimensions;

        if ($dataset === 'workforce' || $dataset === 'DP_WORKFORCE') {
            $query = Employee::query()
                ->where('tenant_id', $tenantId)
                ->with(['department', 'branch', 'jobGrade', 'employmentType']);

            if (!empty($filters['department_id'])) {
                $query->where('department_id', $filters['department_id']);
            }
            if (!empty($filters['branch_id'])) {
                $query->where('branch_id', $filters['branch_id']);
            }
            if (!empty($filters['status'])) {
                $query->where('employment_status', $filters['status']);
            }

            $records = $query->get();

            // Perform Grouping & Aggregations
            $grouped = $records->groupBy(function ($emp) use ($dimensions) {
                $keys = [];
                foreach ($dimensions as $dim) {
                    if ($dim === 'department') $keys[] = $emp->department?->department_name ?? 'Corporate';
                    elseif ($dim === 'branch') $keys[] = $emp->branch?->branch_name ?? 'HQ';
                    elseif ($dim === 'status') $keys[] = $emp->employment_status;
                    elseif ($dim === 'gender') $keys[] = $emp->gender ?? 'Unspecified';
                }
                return implode(' | ', $keys);
            });

            $rows = [];
            foreach ($grouped as $groupKey => $empList) {
                $row = [
                    'group' => $groupKey,
                    'headcount' => $empList->count(),
                    'active_count' => $empList->where('employment_status', 'active')->count(),
                    'fte' => (float) $empList->sum(fn ($e) => ($e->employmentType?->code === 'PART_TIME') ? 0.5 : 1.0),
                ];
                $rows[] = $row;
            }

            return [
                'dataset' => $dataset,
                'total_records_analyzed' => $records->count(),
                'grouped_rows_count' => count($rows),
                'rows' => $rows,
            ];
        }

        // Generic fallback response
        return [
            'dataset' => $dataset,
            'total_records_analyzed' => 0,
            'grouped_rows_count' => 0,
            'rows' => [],
        ];
    }

    public function generateCsvExport(array $reportResult): string
    {
        $rows = $reportResult['rows'] ?? [];
        if (empty($rows)) {
            return "No data available\n";
        }

        $headers = array_keys($rows[0]);
        $csv = implode(',', $headers) . "\n";

        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', array_values($row))) . "\n";
        }

        return $csv;
    }
}
