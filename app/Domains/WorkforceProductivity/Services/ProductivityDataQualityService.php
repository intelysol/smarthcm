<?php

namespace App\Domains\WorkforceProductivity\Services;

use App\Domains\WorkforceProductivity\Enums\DataQualityStatus;
use App\Domains\WorkforceProductivity\Models\HcmProductivityAnomaly;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProductivityDataQualityService
{
    /**
     * Evaluate data quality status to prevent zero-denominator or insufficient data traps.
     */
    public function evaluateQuality(?float $output, ?float $productiveHours, ?float $availableHours): string
    {
        if ($output === null || $productiveHours === null) {
            return DataQualityStatus::INSUFFICIENT_DATA->value;
        }

        if ($productiveHours <= 0.0) {
            return DataQualityStatus::ZERO_DENOMINATOR->value;
        }

        return DataQualityStatus::VALID->value;
    }

    /**
     * Detect anomalies: sudden productivity drops, zero output with attended hours, high waiting time.
     */
    public function detectAnomalies(
        string $tenantId,
        ?string $departmentId,
        float $observedRate,
        float $baselineRate,
        string $detectedDate
    ): ?HcmProductivityAnomaly {
        if ($baselineRate <= 0.0) {
            return null;
        }

        $variancePct = round((($observedRate - $baselineRate) / $baselineRate) * 100.0, 2);

        // Flag if productivity dropped by more than 20% compared to baseline
        if ($variancePct <= -20.0) {
            $severity = $variancePct <= -40.0 ? 'critical' : 'high';

            return HcmProductivityAnomaly::create([
                'id' => Str::uuid()->toString(),
                'tenant_id' => $tenantId,
                'anomaly_type' => 'drop_spike',
                'severity' => $severity,
                'department_id' => $departmentId,
                'detected_date' => $detectedDate,
                'observed_value' => $observedRate,
                'expected_baseline' => $baselineRate,
                'variance_pct' => $variancePct,
                'possible_drivers' => [
                    'Potential process bottleneck',
                    'Waiting time or system downtime',
                    'High absence concentration or new-hire ramp-up',
                ],
                'status' => 'open',
            ]);
        }

        return null;
    }
}
