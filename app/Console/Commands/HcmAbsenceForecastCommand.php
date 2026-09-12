<?php

namespace App\Console\Commands;

use App\Domains\Absence\Services\AbsenceAnalyticsAndForecastingService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class HcmAbsenceForecastCommand extends Command
{
    protected $signature = 'hcm:absence-forecast {tenant : Tenant UUID} {start : Forecast start date} {end : Forecast end date} {--dept= : Optional department filter}';
    protected $description = 'Generate forward-looking absence volume and capacity risk forecasts';

    public function handle(AbsenceAnalyticsAndForecastingService $service): int
    {
        $tenantId = $this->argument('tenant');
        $start = Carbon::parse($this->argument('start'));
        $end = Carbon::parse($this->argument('end'));
        $dept = $this->option('dept');

        $this->info("Generating absence forecast for Tenant: {$tenantId} ({$start->toDateString()} to {$end->toDateString()})...");

        $forecast = $service->generateForecast($tenantId, $start, $end, $dept);
        $this->info("Forecast created! Projected Absence Hours: {$forecast->projected_absence_hours}h, Projected Rate: {$forecast->projected_absence_rate}%, Confidence: {$forecast->confidence_score}.");

        return self::SUCCESS;
    }
}