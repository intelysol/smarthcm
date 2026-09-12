<?php

namespace App\Domains\Engagement\Events;

use App\Domains\Engagement\Models\EngagementSurvey;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EngagementSurveyCreated
{
    use Dispatchable, SerializesModels;
    public function __construct(public EngagementSurvey $survey) {}
}
