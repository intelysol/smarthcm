<?php

declare(strict_types=1);

namespace App\Domains\Performance\Services;

use App\Domains\Performance\Events\PerformanceCalibrationFinalized;
use App\Domains\Performance\Models\PerformanceCalibrationRecord;
use App\Domains\Performance\Models\PerformanceCalibrationSession;
use App\Domains\Performance\Models\PerformanceReview;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PerformanceCalibrationService
{
    /**
     * Create a calibration committee session.
     */
    public function createSession(array $data, User $actor): PerformanceCalibrationSession
    {
        return PerformanceCalibrationSession::create([
            'tenant_id' => $data['tenant_id'],
            'cycle_id' => $data['cycle_id'],
            'name' => $data['name'],
            'status' => 'in_progress',
            'started_at' => Carbon::now(),
        ]);
    }

    /**
     * Adjust an employee rating during calibration with mandatory justification audit reason.
     */
    public function adjustRating(
        PerformanceCalibrationSession $session,
        string $employeeId,
        float $newRating,
        string $reason,
        User $actor
    ): PerformanceCalibrationRecord {
        if (empty($reason)) {
            throw ValidationException::withMessages([
                'change_reason' => ['An auditable reason is required to adjust a calibrated rating.'],
            ]);
        }

        return DB::transaction(function () use ($session, $employeeId, $newRating, $reason, $actor) {
            // Find existing manager rating from review if present
            $review = PerformanceReview::where('cycle_id', $session->cycle_id)
                ->where('employee_id', $employeeId)
                ->first();

            $record = PerformanceCalibrationRecord::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'employee_id' => $employeeId,
                ],
                [
                    'calculated_rating' => $review->calculated_rating ?? $newRating,
                    'proposed_rating' => $review->overall_rating ?? $newRating,
                    'final_rating' => $newRating,
                    'change_reason' => $reason,
                    'changed_by' => $actor->id,
                    'changed_at' => Carbon::now(),
                ]
            );

            if ($review) {
                $review->update([
                    'final_rating' => $newRating,
                    'status' => 'calibrated',
                ]);
            }

            return $record;
        });
    }

    /**
     * Finalize calibration session and lock ratings.
     */
    public function finalizeSession(
        PerformanceCalibrationSession $session,
        User $actor
    ): PerformanceCalibrationSession {
        return DB::transaction(function () use ($session, $actor) {
            $session->update([
                'status' => 'completed',
                'completed_at' => Carbon::now(),
            ]);

            event(new PerformanceCalibrationFinalized($session));

            return $session->fresh();
        });
    }
}
