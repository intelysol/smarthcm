<?php

namespace App\Domains\WorkforceOptimization\Events;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationOpportunity;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkforceOptimizationOpportunityDetected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HcmWorkforceOptimizationOpportunity $opportunity
    ) {}
}
