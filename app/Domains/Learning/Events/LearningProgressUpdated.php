<?php

namespace App\Domains\Learning\Events;

use App\Domains\Learning\Models\LearningProgress;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LearningProgressUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly LearningProgress $progress) {}
}
