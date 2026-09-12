<?php

namespace App\Domains\Career\Events;

use App\Domains\Career\Models\CareerPlan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CareerReadinessCalculated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly CareerPlan $plan,
        public readonly float $readinessScore,
        public readonly string $readinessLevel
    ) {}
}
