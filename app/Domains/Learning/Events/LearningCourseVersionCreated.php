<?php

namespace App\Domains\Learning\Events;

use App\Domains\Learning\Models\LearningCourseVersion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LearningCourseVersionCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly LearningCourseVersion $version) {}
}
