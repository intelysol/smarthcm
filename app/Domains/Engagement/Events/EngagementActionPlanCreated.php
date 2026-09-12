<?php

namespace App\Domains\Engagement\Events;

use App\Domains\Engagement\Models\EngagementActionPlan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EngagementActionPlanCreated
{
    use Dispatchable, SerializesModels;
    public function __construct(public EngagementActionPlan $actionPlan) {}
}
