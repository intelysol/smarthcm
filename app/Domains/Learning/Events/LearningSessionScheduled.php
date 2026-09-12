<?php

namespace App\Domains\Learning\Events;

use App\Domains\Learning\Models\LearningSession;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LearningSessionScheduled
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly LearningSession $session) {}
}
