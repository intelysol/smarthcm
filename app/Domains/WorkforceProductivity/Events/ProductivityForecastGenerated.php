<?php

namespace App\Domains\WorkforceProductivity\Events;

use App\Domains\WorkforceProductivity\Models\HcmProductivityForecast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductivityForecastGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly HcmProductivityForecast $forecast
    ) {}
}
