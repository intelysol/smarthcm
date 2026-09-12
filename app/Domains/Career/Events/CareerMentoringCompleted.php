<?php

namespace App\Domains\Career\Events;

use App\Domains\Career\Models\CareerMentoringRelationship;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CareerMentoringCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly CareerMentoringRelationship $relationship) {}
}
