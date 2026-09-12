<?php

namespace App\Console\Commands;

use App\Domains\Analytics\Services\HcmAnalyticsAlertService;
use App\Domains\Analytics\Services\HcmWorkforceAnalyticsService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Console\Command;

class HcmAnalyticsEvaluateAlertsCommand extends Command
{
    protected $signature = 'hcm:analytics-alerts {--tenant= : Specific Tenant UUID}';

    protected $description = 'Evaluate metric threshold alerts against active metrics and trigger notifications';

    public function handle(HcmAnalyticsAlertService $alertService, HcmWorkforceAnalyticsService $workforceService): int
    {
        $tenantId = $this->option('tenant');
        $this->info("Evaluating HCM analytics alerts...");

        $tenants = $tenantId ? Tenant::where('id', $tenantId)->get() : Tenant::all();

        foreach ($tenants as $t) {
            $headcount = $workforceService->getHeadcountSummary($t->id);
            $turnover = $workforceService->getTurnoverAnalytics($t->id, now()->startOfYear()->toDateString(), now()->toDateString());

            $metricValues = [
                'HCM_HEADCOUNT_ACTIVE' => $headcount['active_headcount'],
                'HCM_TURNOVER_RATE' => $turnover['turnover_rate_percent'],
                'HCM_VOLUNTARY_TURNOVER' => $turnover['voluntary_turnover_rate_percent'],
            ];

            $triggered = $alertService->evaluateAlerts($t->id, $metricValues);
            $this->info("Tenant {$t->name}: evaluated alerts. Triggered: " . count($triggered));
        }

        $this->info("Alert evaluation complete.");

        return 0;
    }
}
