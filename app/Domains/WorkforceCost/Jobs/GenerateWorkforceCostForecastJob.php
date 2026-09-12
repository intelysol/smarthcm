<?php

namespace App\Domains\WorkforceCost\Jobs;

use App\Domains\WorkforceCost\Services\WorkforceCostForecastService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateWorkforceCostForecastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public string $forecastStart,
        public string $forecastEnd,
        public ?string $departmentId = null,
        public array $assumptions = []
    ) {}

    public function handle(WorkforceCostForecastService $service): void
    {
        $service->generateForecast(
            $this->tenantId,
            Carbon::parse($this->forecastStart),
            Carbon::parse($this->forecastEnd),
            $this->departmentId,
            $this->assumptions
        );
    }
}