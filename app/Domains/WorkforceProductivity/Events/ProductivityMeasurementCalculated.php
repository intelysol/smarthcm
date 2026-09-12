<?php

namespace App\Domains\WorkforceProductivity\Events;

use App\Domains\WorkforceProductivity\Models\HcmProductivityMeasurement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductivityMeasurementCalculated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly HcmProductivityMeasurement $measurement
    ) {}
}
