<?php

declare(strict_types=1);

namespace App\Domains\Performance\Services;

use App\Domains\Performance\Events\PerformanceReviewAcknowledged;
use App\Domains\Performance\Events\PerformanceReviewPublished;
use App\Domains\Performance\Models\PerformanceFinalOutcome;
use App\Domains\Performance\Models\PerformanceReviewAppeal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PerformanceFinalOutcomeService
{
    /**
     * Publish finalized performance outcome for employee.
     */
    public function publishOutcome(array $data, User $actor): PerformanceFinalOutcome
    {
        return DB::transaction(function () use ($data, $actor) {
            $outcome = PerformanceFinalOutcome::updateOrCreate(
                [
                    'tenant_id' => $data['tenant_id'],
                    'cycle_id' => $data['cycle_id'],
                    'employee_id' => $data['employee_id'],
                ],
                [
                    'final_rating' => $data['final_rating'],
                    'final_score' => $data['final_score'] ?? null,
                    'summary' => $data['summary'] ?? null,
                    'strengths' => $data['strengths'] ?? null,
                    'development_areas' => $data['development_areas'] ?? null,
                    'finalized_by' => $actor->id,
                    'finalized_at' => Carbon::now(),
                    'acknowledgement_status' => 'pending',
                ]
            );

            event(new PerformanceReviewPublished($outcome));

            return $outcome;
        });
    }

    /**
     * Employee acknowledges review with optional comment.
     * Crucial HR Principle: Acknowledgement confirms receipt, not necessarily agreement.
     */
    public function acknowledgeReview(
        PerformanceFinalOutcome $outcome,
        ?string $comment = null
    ): PerformanceFinalOutcome {
        $outcome->update([
            'acknowledgement_status' => 'acknowledged',
            'employee_acknowledged_at' => Carbon::now(),
            'employee_comment' => $comment,
        ]);

        event(new PerformanceReviewAcknowledged($outcome));

        return $outcome->fresh();
    }

    /**
     * Submit an appeal / dispute on a finalized review.
     */
    public function submitAppeal(
        string $tenantId,
        string $reviewId,
        string $employeeId,
        string $reason
    ): PerformanceReviewAppeal {
        return PerformanceReviewAppeal::create([
            'tenant_id' => $tenantId,
            'review_id' => $reviewId,
            'employee_id' => $employeeId,
            'reason' => $reason,
            'submitted_at' => Carbon::now(),
            'status' => 'submitted',
        ]);
    }

    /**
     * Resolve appeal by HR.
     */
    public function resolveAppeal(
        PerformanceReviewAppeal $appeal,
        string $resolution,
        User $actor
    ): PerformanceReviewAppeal {
        $appeal->update([
            'status' => 'resolved',
            'resolution' => $resolution,
            'resolved_by' => $actor->id,
            'resolved_at' => Carbon::now(),
        ]);

        return $appeal->fresh();
    }
}
