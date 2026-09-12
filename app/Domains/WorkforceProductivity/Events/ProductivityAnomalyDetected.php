<?php

namespace App\Domains\WorkforceProductivity\Events;

use App\Domains\WorkforceProductivity\Models\HcmProductivityAnomaly;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductivityAnomalyDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly HcmProductivityAnomaly $anomaly
    ) {}
}
