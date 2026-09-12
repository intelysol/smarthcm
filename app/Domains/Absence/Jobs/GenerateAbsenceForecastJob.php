<?php

namespace App\Domains\Absence\Jobs;

use App\Domains\Absence\Services\AbsenceAnalyticsAndForecastingService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAbsenceForecastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public string $forecastStart,
        public string $forecastEnd,
        public ?string $departmentId = null
    ) {
    }

    public function handle(AbsenceAnalyticsAndForecastingService $service): void
    {
        $service->generateForecast(
            $this->tenantId,
            Carbon::parse($this->forecastStart),
            Carbon::parse($this->forecastEnd),
            $this->departmentId
        );
    }
}