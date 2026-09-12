<?php

namespace App\Domains\ResponsibleAi\Services;

use App\Domains\ResponsibleAi\Models\HcmAiGovFairnessCheck;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AiFairnessMonitoringService
{
    public function evaluateParity(string $tenantId, string $metricName, array $sampleDistribution, float $threshold = 10.0): HcmAiGovFairnessCheck
    {
        // Calculate max variance between aggregate cohorts
        $rates = array_values($sampleDistribution);
        $maxRate = max($rates);
        $minRate = min($rates);
        $variance = $maxRate > 0 ? round((($maxRate - $minRate) / $maxRate) * 100, 2) : 0.0;

        $status = $variance <= $threshold ? 'COMPLIANT' : 'WARNING';

        return HcmAiGovFairnessCheck::create([
            'tenant_id' => $tenantId,
            'check_code' => 'FNC-' . Str::upper(Str::random(6)),
            'metric_evaluated' => $metricName,
            'variance_percentage' => $variance,
            'threshold_allowed' => $threshold,
            'fairness_status' => $status,
            'aggregate_data_sample' => $sampleDistribution,
            'investigation_notes' => $status === 'WARNING' ? 'Disparate recommendation rate variance detected between approved cohorts. Stewardship investigation required.' : 'Cohort distribution meets statistical parity requirements.',
            'evaluated_at' => Carbon::now(),
        ]);
    }
}
