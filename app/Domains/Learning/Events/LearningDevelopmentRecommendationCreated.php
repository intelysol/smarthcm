<?php

namespace App\Domains\Learning\Events;

use App\Domains\Learning\Models\LearningRecommendation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LearningDevelopmentRecommendationCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly LearningRecommendation $recommendation) {}
}
