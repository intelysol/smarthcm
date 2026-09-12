<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Models\HcmAnalyticsAlert;
use App\Domains\Analytics\Models\HcmAnalyticsAlertSubscription;

class HcmAnalyticsAlertService
{
    public function evaluateAlerts(string $tenantId, array $metricValues): array
    {
        $alerts = HcmAnalyticsAlert::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['metric', 'subscriptions'])
            ->get();

        $triggeredAlerts = [];

        foreach ($alerts as $alert) {
            $metricCode = $alert->metric?->code;
            if (! isset($metricValues[$metricCode])) {
                continue;
            }

            $currentValue = (float) $metricValues[$metricCode];
            $threshold = (float) $alert->threshold_value;
            $op = $alert->comparison_operator;

            $isTriggered = match ($op) {
                '>' => $currentValue > $threshold,
                '>=' => $currentValue >= $threshold,
                '<' => $currentValue < $threshold,
                '<=' => $currentValue <= $threshold,
                '==' => $currentValue == $threshold,
                '!=' => $currentValue != $threshold,
                default => false,
            };

            if ($isTriggered) {
                $triggeredAlerts[] = [
                    'alert_id' => $alert->id,
                    'title' => $alert->title,
                    'severity' => $alert->severity,
                    'metric_code' => $metricCode,
                    'current_value' => $currentValue,
                    'threshold_value' => $threshold,
                    'operator' => $op,
                ];

                foreach ($alert->subscriptions as $sub) {
                    $sub->update(['last_triggered_at' => now()]);
                }
            }
        }

        return $triggeredAlerts;
    }
}
