<?php

namespace App\Domains\Career\Events;

use App\Domains\Career\Models\CareerPlanAction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CareerDevelopmentActionCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly CareerPlanAction $action) {}
}
