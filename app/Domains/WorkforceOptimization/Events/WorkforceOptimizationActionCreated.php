<?php

namespace App\Domains\WorkforceOptimization\Events;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationAction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkforceOptimizationActionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HcmWorkforceOptimizationAction $action
    ) {}
}
