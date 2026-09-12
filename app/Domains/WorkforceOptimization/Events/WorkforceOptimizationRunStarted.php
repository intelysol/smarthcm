<?php

namespace App\Domains\WorkforceOptimization\Events;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRun;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkforceOptimizationRunStarted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HcmWorkforceOptimizationRun $run
    ) {}
}
