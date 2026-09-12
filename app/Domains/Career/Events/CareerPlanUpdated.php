<?php

namespace App\Domains\Career\Events;

use App\Domains\Career\Models\CareerPlan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CareerPlanUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly CareerPlan $plan) {}
}
