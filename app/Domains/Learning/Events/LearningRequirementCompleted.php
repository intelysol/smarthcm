<?php

namespace App\Domains\Learning\Events;

use App\Domains\Learning\Models\LearningRequirementAssignment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LearningRequirementCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly LearningRequirementAssignment $assignment) {}
}
