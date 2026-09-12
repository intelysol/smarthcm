<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Services;

use App\Domains\Compensation\Models\CompensationCalibrationRecord;
use App\Domains\Compensation\Models\CompensationCalibrationSession;
use App\Domains\Compensation\Models\CompensationCycle;
use App\Domains\Compensation\Models\CompensationRecommendation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CompensationCalibrationService
{
    public function __construct(
        protected CompensationCalculationEngine $calculationEngine
    ) {}

    public function createSession(
        User $user,
        CompensationCycle $cycle,
        string $title,
        string $scopeType = 'department',
        ?string $scopeId = null,
        ?string $sessionDate = null,
        array $facilitators = []
    ): CompensationCalibrationSession {
        return CompensationCalibrationSession::create([
            'tenant_id' => $user->tenant_id,
            'compensation_cycle_id' => $cycle->id,
            'title' => $title,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'session_date' => $sessionDate ?? now(),
            'facilitators' => $facilitators,
            'status' => 'scheduled',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    public function recordAdjustment(
        User $calibrator,
        CompensationCalibrationSession $session,
        CompensationRecommendation $recommendation,
        float $calibratedPercentage,
        string $mandatoryReason
    ): CompensationCalibrationRecord {
        $reason = trim($mandatoryReason);
        if (strlen($reason) < 10) {
            throw ValidationException::withMessages([
                'mandatory_calibration_reason' => 'A specific calibration reason of at least 10 characters is strictly required for auditing.',
            ]);
        }

        $currentBase = (float) $recommendation->current_base_salary;
        $originalPct = (float) $recommendation->increase_percentage;
        $originalAmount = (float) $recommendation->increase_amount;

        $newMath = $this->calculationEngine->computeSalaryFromPercentage($currentBase, $calibratedPercentage);
        $calibratedAmount = $newMath['increase_amount'];
        $calibratedSalary = $newMath['recommended_base_salary'];

        // Audit Record
        $record = CompensationCalibrationRecord::create([
            'tenant_id' => $calibrator->tenant_id,
            'compensation_calibration_session_id' => $session->id,
            'compensation_recommendation_id' => $recommendation->id,
            'original_increase_pct' => $originalPct,
            'original_increase_amount' => $originalAmount,
            'calibrated_increase_pct' => $calibratedPercentage,
            'calibrated_increase_amount' => $calibratedAmount,
            'mandatory_calibration_reason' => $reason,
            'calibrated_by' => $calibrator->id,
            'calibrated_at' => now(),
        ]);

        // Update Recommendation with calibrated figures
        $recommendation->update([
            'increase_percentage' => $calibratedPercentage,
            'increase_amount' => $calibratedAmount,
            'recommended_base_salary' => $calibratedSalary,
            'status' => 'calibrated',
        ]);

        return $record;
    }

    public function completeSession(CompensationCalibrationSession $session): CompensationCalibrationSession
    {
        $session->update(['status' => 'completed']);

        return $session->fresh();
    }
}
