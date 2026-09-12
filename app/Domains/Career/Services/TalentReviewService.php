<?php

namespace App\Domains\Career\Services;

use App\Domains\Career\Enums\NineBoxPosition;
use App\Domains\Career\Enums\TalentReviewStatus;
use App\Domains\Career\Events\TalentReviewCompleted;
use App\Domains\Career\Events\TalentReviewCreated;
use App\Domains\Career\Models\TalentReviewRecord;
use App\Domains\Career\Models\TalentReviewSession;
use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Support\Facades\DB;

class TalentReviewService
{
    public function __construct(protected AuditService $audit) {}

    public function createSession(
        string $tenantId,
        string $title,
        string $scopeType = 'organization',
        ?string $scopeId = null,
        ?string $reviewDate = null
    ): TalentReviewSession {
        return DB::transaction(function () use ($tenantId, $title, $scopeType, $scopeId, $reviewDate) {
            $session = TalentReviewSession::query()->create([
                'tenant_id' => $tenantId,
                'title' => $title,
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
                'review_date' => $reviewDate ?? now()->toDateString(),
                'status' => TalentReviewStatus::Draft->value,
            ]);

            TalentReviewCreated::dispatch($session);

            $this->audit->record(
                $tenantId,
                'TalentReviewCreated',
                'create_talent_review_session',
                TalentReviewSession::class,
                (string) $session->id,
                null,
                null,
                ['title' => $title, 'scope_type' => $scopeType]
            );

            return $session;
        });
    }

    public function recordPlacement(
        TalentReviewSession $session,
        Employee $employee,
        float $performanceRating,
        float $potentialRating,
        string $readinessLevel = 'developing',
        string $retentionRisk = 'low',
        string $vacancyRisk = 'low',
        string $mobilityRating = 'high',
        string $developmentPriority = 'core_development',
        ?string $managerNotes = null
    ): TalentReviewRecord {
        $nineBox = $this->determineNineBoxPosition($performanceRating, $potentialRating);

        return TalentReviewRecord::query()->updateOrCreate(
            [
                'tenant_id' => $session->tenant_id,
                'session_id' => $session->id,
                'employee_id' => $employee->id,
            ],
            [
                'performance_rating' => $performanceRating,
                'potential_rating' => $potentialRating,
                'nine_box_position' => $nineBox->value,
                'readiness_level' => $readinessLevel,
                'retention_risk' => $retentionRisk,
                'vacancy_risk' => $vacancyRisk,
                'mobility_rating' => $mobilityRating,
                'development_priority' => $developmentPriority,
                'manager_notes' => $managerNotes,
            ]
        );
    }

    public function overridePlacement(
        TalentReviewRecord $record,
        NineBoxPosition $newPosition,
        Employee $overridingUser,
        string $reason,
        ?string $calibrationNotes = null
    ): TalentReviewRecord {
        $prevPosition = $record->nine_box_position;

        $record->update([
            'nine_box_position' => $newPosition->value,
            'is_overridden' => true,
            'override_reason' => $reason,
            'calibration_notes' => $calibrationNotes,
        ]);

        $this->audit->record(
            (string) $record->tenant_id,
            'TalentNineBoxOverridden',
            'nine_box_override',
            TalentReviewRecord::class,
            (string) $record->id,
            null,
            ['nine_box_position' => $prevPosition],
            [
                'nine_box_position' => $newPosition->value,
                'employee_id' => $record->employee_id,
                'overridden_by' => $overridingUser->id,
                'reason' => $reason,
            ]
        );

        return $record;
    }

    public function completeSession(TalentReviewSession $session, Employee $finalizer): TalentReviewSession
    {
        return DB::transaction(function () use ($session, $finalizer) {
            $session->update([
                'status' => TalentReviewStatus::Completed->value,
                'finalized_by' => $finalizer->id,
                'finalized_at' => now(),
            ]);

            TalentReviewCompleted::dispatch($session);
            return $session;
        });
    }

    public function determineNineBoxPosition(float $performance, float $potential): NineBoxPosition
    {
        $perfBand = match (true) {
            $performance >= 4.0 => 'high',
            $performance >= 3.0 => 'medium',
            default => 'low',
        };

        $potBand = match (true) {
            $potential >= 4.0 => 'high',
            $potential >= 3.0 => 'medium',
            default => 'low',
        };

        return match ("{$perfBand}_{$potBand}") {
            'high_high' => NineBoxPosition::HighPerfHighPot,
            'high_medium' => NineBoxPosition::HighPerfMedPot,
            'high_low' => NineBoxPosition::HighPerfLowPot,
            'medium_high' => NineBoxPosition::MedPerfHighPot,
            'medium_medium' => NineBoxPosition::MedPerfMedPot,
            'medium_low' => NineBoxPosition::MedPerfLowPot,
            'low_high' => NineBoxPosition::LowPerfHighPot,
            'low_medium' => NineBoxPosition::LowPerfMedPot,
            default => NineBoxPosition::LowPerfLowPot,
        };
    }
}
