<?php

namespace App\Domains\WorkforceOptimization\Events;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRecommendation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkforceOptimizationRecommendationGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HcmWorkforceOptimizationRecommendation $recommendation
    ) {}
}
