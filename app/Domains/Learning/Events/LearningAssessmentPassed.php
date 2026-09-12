<?php

namespace App\Domains\Learning\Events;

use App\Domains\Learning\Models\LearningAssessmentAttempt;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LearningAssessmentPassed
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly LearningAssessmentAttempt $attempt) {}
}
