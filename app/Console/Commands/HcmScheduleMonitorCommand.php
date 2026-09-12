<?php

namespace App\Console\Commands;

use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Services\Scheduling\RealTimeCoverageService;
use App\Domains\Attendance\Services\Scheduling\ScheduleValidationService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Console\Command;

class HcmScheduleMonitorCommand extends Command
{
    protected $signature = 'hcm:schedule-monitor {--tenant= : Tenant ID} {--date= : Date to monitor (YYYY-MM-DD)}';

    protected $description = 'Monitor real-time schedule coverage, detect no-shows, and validate active roster periods';

    public function handle(
        ScheduleValidationService $validationService,
        RealTimeCoverageService $realTimeService
    ): int {
        $tenantId = $this->option('tenant');
        $date = $this->option('date') ?: now()->toDateString();

        $tenants = $tenantId
            ? Tenant::query()->where('id', $tenantId)->get()
            : Tenant::query()->get();

        $this->info("Running HCM Schedule Monitor for date: {$date} across " . $tenants->count() . ' tenant(s)...');

        foreach ($tenants as $tenant) {
            $this->line("Evaluating Tenant: {$tenant->name} ({$tenant->id})");

            // 1. Evaluate real-time operational coverage
            $coverageResult = $realTimeService->evaluateRealTimeCoverage($tenant->id, $date);
            $this->info(" - Real-Time Coverage: {$coverageResult['real_time_coverage_pct']}% (Present: {$coverageResult['total_present']}/{$coverageResult['total_scheduled']})");
            if ($coverageResult['exceptions_generated'] > 0) {
                $this->warn(" - Generated {$coverageResult['exceptions_generated']} schedule exception(s) [No-shows/Late].");
            }

            // 2. Validate current published/approved periods
            $periods = RosterPeriod::query()
                ->where('tenant_id', $tenant->id)
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->get();

            foreach ($periods as $period) {
                $validation = $validationService->validatePeriod($period);
                $this->line(" - Period '{$period->name}': Status = {$validation['validation_status']}, Quality Score = {$validation['schedule_quality_score']}%");
            }
        }

        $this->info('HCM Schedule Monitoring complete.');
        return Command::SUCCESS;
    }
}
