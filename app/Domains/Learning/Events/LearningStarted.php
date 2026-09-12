<?php

namespace App\Domains\Learning\Events;

use App\Domains\Learning\Models\LearningEnrollment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LearningStarted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly LearningEnrollment $enrollment) {}
}
