<?php

namespace App\Domains\WorkforceProductivity\Events;

use App\Domains\WorkforceProductivity\Models\HcmProductivityRoiCalculation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkforceROICalculated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly HcmProductivityRoiCalculation $calculation
    ) {}
}
