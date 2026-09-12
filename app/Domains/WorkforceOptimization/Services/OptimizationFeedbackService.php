<?php

namespace App\Domains\WorkforceOptimization\Services;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationFeedback;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRecommendation;
use App\Models\User;

class OptimizationFeedbackService
{
    /**
     * Submit feedback / tuning from operational managers on recommendations.
     */
    public function recordFeedback(
        HcmWorkforceOptimizationRecommendation $recommendation,
        User $user,
        string $feedbackType,
        ?string $reasonCode = null,
        ?string $narrative = null,
        int $feasibilityScore = 80,
        int $practicalityScore = 80,
        array $adjustments = []
    ): HcmWorkforceOptimizationFeedback {
        return HcmWorkforceOptimizationFeedback::create([
            'tenant_id' => $recommendation->tenant_id,
            'recommendation_id' => $recommendation->id,
            'feedback_type' => $feedbackType,
            'reason' => "{$reasonCode}: {$narrative}",
            'user_id' => $user->id,
        ]);
    }
}
