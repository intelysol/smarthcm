<?php

namespace App\Domains\WorkforceProductivity\Services;

use App\Domains\WorkforceProductivity\Models\HcmProductivityBenchmark;
use Illuminate\Support\Str;

class ProductivityBenchmarkService
{
    /**
     * Compute normalized internal benchmark and productivity index (baseline = 100).
     */
    public function createBenchmark(
        string $tenantId,
        string $benchmarkName,
        string $benchmarkType,
        string $baseStart,
        string $baseEnd,
        float $baseRate,
        string $compStart,
        string $compEnd,
        float $compRate,
        array $extraData = []
    ): HcmProductivityBenchmark {
        $normalizedIndex = $baseRate > 0
            ? round(($compRate / $baseRate) * 100.0, 2)
            : 100.00;

        $variancePct = $baseRate > 0
            ? round((($compRate - $baseRate) / $baseRate) * 100.0, 2)
            : 0.0;

        return HcmProductivityBenchmark::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'benchmark_name' => $benchmarkName,
            'benchmark_type' => $benchmarkType,
            'baseline_period_start' => $baseStart,
            'baseline_period_end' => $baseEnd,
            'baseline_productivity_rate' => round($baseRate, 4),
            'comparison_period_start' => $compStart,
            'comparison_period_end' => $compEnd,
            'comparison_productivity_rate' => round($compRate, 4),
            'normalized_index' => $normalizedIndex,
            'variance_pct' => $variancePct,
            'benchmark_data' => $extraData,
        ]);
    }
}
