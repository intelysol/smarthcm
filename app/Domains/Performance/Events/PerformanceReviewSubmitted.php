<?php

declare(strict_types=1);

namespace App\Domains\Performance\Events;

use App\Domains\Performance\Models\PerformanceCalibrationSession;
use App\Domains\Performance\Models\PerformanceCheckin;
use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Models\PerformanceFeedbackRequest;
use App\Domains\Performance\Models\PerformanceFeedbackResponse;
use App\Domains\Performance\Models\PerformanceFinalOutcome;
use App\Domains\Performance\Models\PerformanceGoal;
use App\Domains\Performance\Models\PerformanceImprovementPlan;
use App\Domains\Performance\Models\PerformanceReview;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PerformanceReviewSubmitted
{
    use Dispatchable, SerializesModels;
    public function __construct(public PerformanceReview $review) {}
}
