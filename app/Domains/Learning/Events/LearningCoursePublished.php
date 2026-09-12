<?php

namespace App\Domains\Learning\Events;

use App\Domains\Learning\Models\LearningCourse;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LearningCoursePublished
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly LearningCourse $course) {}
}
