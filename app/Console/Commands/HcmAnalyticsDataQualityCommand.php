<?php

namespace App\Console\Commands;

use App\Domains\Analytics\Services\HcmDataQualityService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Console\Command;

class HcmAnalyticsDataQualityCommand extends Command
{
    protected $signature = 'hcm:analytics-quality {--tenant= : Specific Tenant UUID}';

    protected $description = 'Run data quality and integrity validations across HCM domains';

    public function handle(HcmDataQualityService $qualityService): int
    {
        $tenantId = $this->option('tenant');
        $this->info("Starting HCM data quality evaluation...");

        $tenants = $tenantId ? Tenant::where('id', $tenantId)->get() : Tenant::all();

        foreach ($tenants as $t) {
            $summary = $qualityService->runDataQualityValidation($t->id);
            $this->info("Tenant {$t->name}: Overall Data Quality Score: {$summary['overall_data_quality_score']}% ({$summary['checks_evaluated_count']} rules evaluated)");
        }

        $this->info("Data quality validation complete.");

        return 0;
    }
}
