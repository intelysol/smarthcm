<?php

declare(strict_types=1);

namespace App\Domains\Performance\Services;

use App\Domains\Performance\Events\PerformanceReviewSubmitted;
use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Models\PerformanceReview;
use App\Domains\Performance\Models\PerformanceReviewVersion;
use App\Domains\Performance\Models\PerformanceSelfAssessment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PerformanceReviewService
{
    /**
     * Submit employee self-assessment.
     */
    public function submitSelfAssessment(
        PerformanceReview $review,
        array $data,
        ?User $actor = null
    ): PerformanceSelfAssessment {
        return DB::transaction(function () use ($review, $data, $actor) {
            $assessment = PerformanceSelfAssessment::updateOrCreate(
                [
                    'tenant_id' => $review->tenant_id,
                    'review_id' => $review->id,
                ],
                [
                    'goal_assessment' => $data['goal_assessment'] ?? null,
                    'competency_assessment' => $data['competency_assessment'] ?? null,
                    'achievements' => $data['achievements'] ?? null,
                    'challenges' => $data['challenges'] ?? null,
                    'development' => $data['development'] ?? null,
                    'overall_self_rating' => $data['overall_self_rating'] ?? null,
                    'comments' => $data['comments'] ?? null,
                    'submitted_at' => Carbon::now(),
                ]
            );

            $review->update([
                'status' => 'self_reviewed',
            ]);

            // Save snapshot version
            PerformanceReviewVersion::create([
                'review_id' => $review->id,
                'version' => $review->version ?? 1,
                'snapshot' => [
                    'stage' => 'self_assessment',
                    'data' => $data,
                ],
                'created_by' => $actor?->id,
            ]);

            event(new PerformanceReviewSubmitted($review));

            return $assessment;
        });
    }

    /**
     * Submit manager review assessment.
     */
    public function submitManagerAssessment(
        PerformanceReview $review,
        array $data,
        User $actor
    ): PerformanceReview {
        return DB::transaction(function () use ($review, $data, $actor) {
            $newVersion = ($review->version ?? 1) + 1;
            $review->update([
                'status' => 'manager_reviewed',
                'reviewer_id' => $actor->id,
                'overall_rating' => $data['overall_rating'] ?? null,
                'calculated_rating' => $data['calculated_rating'] ?? ($data['overall_rating'] ?? null),
                'summary' => $data['summary'] ?? $review->summary,
                'submitted_at' => Carbon::now(),
                'version' => $newVersion,
            ]);

            // Save snapshot version
            PerformanceReviewVersion::create([
                'review_id' => $review->id,
                'version' => $newVersion,
                'snapshot' => [
                    'stage' => 'manager_assessment',
                    'data' => $data,
                ],
                'created_by' => $actor->id,
            ]);

            event(new PerformanceReviewSubmitted($review));

            return $review->fresh();
        });
    }
}
