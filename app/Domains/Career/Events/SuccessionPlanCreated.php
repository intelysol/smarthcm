<?php

namespace App\Domains\Career\Events;

use App\Domains\Career\Models\SuccessionPlan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SuccessionPlanCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly SuccessionPlan $plan) {}
}
