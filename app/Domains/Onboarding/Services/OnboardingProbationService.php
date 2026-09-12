<?php

namespace App\Domains\Onboarding\Services;

use App\Domains\Onboarding\Enums\ProbationStatus;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Models\HcmOnboardingProbation;
use App\Domains\Onboarding\Models\HcmOnboardingProbationReview;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OnboardingProbationService
{
    public function extendProbation(HcmOnboardingProbation $probation, string $newEndDate, string $reason): HcmOnboardingProbation
    {
        $parsedNewEnd = Carbon::parse($newEndDate);
        if ($parsedNewEnd->lessThanOrEqualTo($probation->probation_end_date)) {
            throw ValidationException::withMessages(['new_end_date' => 'Extended probation end date must be after original end date.']);
        }

        $probation->update([
            'extended_to_date' => $parsedNewEnd->toDateString(),
            'status' => ProbationStatus::EXTENDED->value,
            'outcome' => 'extended',
        ]);

        return $probation;
    }

    public function completeProbationReview(HcmOnboardingProbation $probation, int $reviewerId, array $data): HcmOnboardingProbationReview
    {
        return DB::transaction(function () use ($probation, $reviewerId, $data) {
            $recommendation = $data['recommendation'] ?? 'pass';

            $review = HcmOnboardingProbationReview::create([
                'tenant_id' => $probation->tenant_id,
                'probation_id' => $probation->id,
                'reviewer_id' => $reviewerId,
                'performance_rating' => $data['performance_rating'] ?? 4.0,
                'recommendation' => $recommendation,
                'comments' => $data['comments'] ?? null,
                'reviewed_at' => now(),
            ]);

            $finalStatus = $recommendation === 'pass' ? ProbationStatus::PASSED->value : ($recommendation === 'extend' ? ProbationStatus::EXTENDED->value : ProbationStatus::FAILED->value);
            $outcome = $recommendation === 'pass' ? 'confirmed' : ($recommendation === 'extend' ? 'extended' : 'terminated');

            $probation->update([
                'status' => $finalStatus,
                'outcome' => $outcome,
            ]);

            return $review;
        });
    }
}
