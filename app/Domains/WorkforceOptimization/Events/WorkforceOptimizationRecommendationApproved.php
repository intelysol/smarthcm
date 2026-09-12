<?php

namespace App\Domains\WorkforceOptimization\Events;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRecommendation;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkforceOptimizationRecommendationApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HcmWorkforceOptimizationRecommendation $recommendation,
        public ?User $approver = null
    ) {}
}
