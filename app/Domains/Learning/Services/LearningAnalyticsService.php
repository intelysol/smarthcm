<?php

namespace App\Domains\Learning\Services;

use App\Domains\Analytics\Services\AnalyticsService;
use App\Domains\Learning\Models\EmployeeLearningRecord;
use App\Domains\Learning\Models\LearningCertificate;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Learning\Models\LearningRequirementAssignment;
use App\Domains\Learning\Models\LearningTrainingCost;

class LearningAnalyticsService
{
    public function __construct(
        private readonly AnalyticsService $analytics
    ) {}

    /**
     * Compute and ingest learning domain facts into the Analytics Platform.
     *
     * @return array<string, mixed>
     */
    public function generateSnapshot(string $tenantId): array
    {
        $totalEnrollments = LearningEnrollment::query()->where('tenant_id', $tenantId)->count();
        $completedEnrollments = LearningEnrollment::query()->where('tenant_id', $tenantId)->where('status', 'completed')->count();
        $completionRate = $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100, 2) : 0.0;

        $totalMandatory = LearningRequirementAssignment::query()->where('tenant_id', $tenantId)->count();
        $completedMandatory = LearningRequirementAssignment::query()->where('tenant_id', $tenantId)->where('status', 'completed')->count();
        $mandatoryCompletionRate = $totalMandatory > 0 ? round(($completedMandatory / $totalMandatory) * 100, 2) : 0.0;

        $avgScore = (float) (EmployeeLearningRecord::query()->where('tenant_id', $tenantId)->avg('final_score') ?? 0.0);
        $totalHours = (float) (EmployeeLearningRecord::query()->where('tenant_id', $tenantId)->sum('learning_hours') ?? 0.0);
        $totalCost = (float) (LearningTrainingCost::query()->where('tenant_id', $tenantId)->sum('amount') ?? 0.0);

        $totalCerts = LearningCertificate::query()->where('tenant_id', $tenantId)->count();
        $expiredCerts = LearningCertificate::query()->where('tenant_id', $tenantId)->where('status', 'expired')->count();
        $certExpiryRate = $totalCerts > 0 ? round(($expiredCerts / $totalCerts) * 100, 2) : 0.0;

        $facts = [
            [
                'fact_type' => 'learning_metrics',
                'fact_date' => now()->toDateString(),
                'dimensions' => ['source' => 'lms_snapshot'],
                'measures' => [
                    'learning_completion_rate' => $completionRate,
                    'mandatory_training_completion_rate' => $mandatoryCompletionRate,
                    'average_course_score' => round($avgScore, 2),
                    'training_hours' => $totalHours,
                    'training_cost' => $totalCost,
                    'certification_expiry_rate' => $certExpiryRate,
                ],
            ]
        ];

        $this->analytics->ingest($tenantId, $facts);

        return [
            'learning_completion_rate' => $completionRate,
            'mandatory_training_completion_rate' => $mandatoryCompletionRate,
            'average_course_score' => round($avgScore, 2),
            'training_hours' => $totalHours,
            'training_cost' => $totalCost,
            'certification_expiry_rate' => $certExpiryRate,
        ];
    }
}
