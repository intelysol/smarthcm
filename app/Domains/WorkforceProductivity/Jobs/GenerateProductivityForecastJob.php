<?php

namespace App\Domains\WorkforceProductivity\Jobs;

use App\Domains\WorkforceProductivity\Events\ProductivityForecastGenerated;
use App\Domains\WorkforceProductivity\Services\ProductivityForecastService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateProductivityForecastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly ?string $departmentId,
        public readonly string $forecastStart,
        public readonly string $forecastEnd,
        public readonly array $assumptions = []
    ) {}

    public function handle(ProductivityForecastService $service): void
    {
        $forecast = $service->generateForecast(
            $this->tenantId,
            $this->departmentId,
            $this->forecastStart,
            $this->forecastEnd,
            $this->assumptions
        );

        event(new ProductivityForecastGenerated($forecast));
    }
}
