<?php

declare(strict_types=1);

namespace App\Domains\Performance\Services;

use App\Domains\Performance\Models\Competency;
use App\Domains\Performance\Models\PerformanceCompetencyAssessment;
use App\Domains\Performance\Models\PerformanceReview;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PerformanceCompetencyService
{
    /**
     * Record a competency assessment on a performance review.
     */
    public function recordAssessment(
        PerformanceReview $review,
        string $competencyId,
        float $rating,
        ?string $comment = null,
        ?string $evidence = null
    ): PerformanceCompetencyAssessment {
        return PerformanceCompetencyAssessment::updateOrCreate(
            [
                'review_id' => $review->id,
                'competency_id' => $competencyId,
            ],
            [
                'rating' => $rating,
                'comment' => $comment,
                'evidence' => $evidence,
            ]
        );
    }

    /**
     * Compute competency score and identify competency gaps against an expected benchmark level.
     */
    public function evaluateGaps(PerformanceReview $review, array $expectedLevels): array
    {
        $assessments = PerformanceCompetencyAssessment::where('review_id', $review->id)
            ->with('competency')
            ->get();

        $gaps = [];
        foreach ($assessments as $assessment) {
            $expected = $expectedLevels[$assessment->competency_id] ?? 3.0; // default baseline level
            $actual = (float) $assessment->rating;
            $gap = $expected - $actual;

            if ($gap > 0) {
                $gaps[] = [
                    'competency_id' => $assessment->competency_id,
                    'competency_name' => $assessment->competency?->name ?? 'Competency',
                    'expected_level' => $expected,
                    'observed_level' => $actual,
                    'gap' => $gap,
                    'is_development_need' => true,
                ];
            }
        }

        return $gaps;
    }
}
