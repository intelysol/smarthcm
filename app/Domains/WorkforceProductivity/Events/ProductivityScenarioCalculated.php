<?php

namespace App\Domains\WorkforceProductivity\Events;

use App\Domains\WorkforceProductivity\Models\HcmProductivityScenario;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductivityScenarioCalculated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly HcmProductivityScenario $scenario
    ) {}
}
