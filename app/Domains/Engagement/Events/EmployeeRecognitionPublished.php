<?php

namespace App\Domains\Engagement\Events;

use App\Domains\Engagement\Models\EngagementRecognition;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeRecognitionPublished
{
    use Dispatchable, SerializesModels;
    public function __construct(public EngagementRecognition $recognition) {}
}
