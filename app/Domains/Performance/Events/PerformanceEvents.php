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

class PerformanceCycleOpened
{
    use Dispatchable, SerializesModels;
    public function __construct(public PerformanceCycle $cycle) {}
}

class PerformanceCycleClosed
{
    use Dispatchable, SerializesModels;
    public function __construct(public PerformanceCycle $cycle) {}
}

class PerformanceGoalCreated
{
    use Dispatchable, SerializesModels;
    public function __construct(public PerformanceGoal $goal) {}
}

class PerformanceGoalProgressUpdated
{
    use Dispatchable, SerializesModels;
    public function __construct(public PerformanceGoal $goal, public float $progress) {}
}

class PerformanceCheckinCompleted
{
    use Dispatchable, SerializesModels;
    public function __construct(public PerformanceCheckin $checkin) {}
}

class PerformanceFeedbackRequested
{
    use Dispatchable, SerializesModels;
    public function __construct(public PerformanceFeedbackRequest $request) {}
}

class PerformanceFeedbackSubmitted
{
    use Dispatchable, SerializesModels;
    public function __construct(public PerformanceFeedbackResponse $response) {}
}

class PerformanceReviewSubmitted
{
    use Dispatchable, SerializesModels;
    public function __construct(public PerformanceReview $review) {}
}

class PerformanceCalibrationFinalized
{
    use Dispatchable, SerializesModels;
    public function __construct(public PerformanceCalibrationSession $session) {}
}

class PerformanceReviewPublished
{
    use Dispatchable, SerializesModels;
    public function __construct(public PerformanceFinalOutcome $outcome) {}
}

class PerformanceReviewAcknowledged
{
    use Dispatchable, SerializesModels;
    public function __construct(public PerformanceFinalOutcome $outcome) {}
}

class PerformancePipCreated
{
    use Dispatchable, SerializesModels;
    public function __construct(public PerformanceImprovementPlan $pip) {}
}
