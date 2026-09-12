<?php

namespace App\Domains\Career\Services;

use App\Domains\Career\Enums\CareerReadinessLevel;
use App\Domains\Career\Events\CareerReadinessCalculated;
use App\Domains\Career\Events\CareerReadinessChanged;
use App\Domains\Career\Models\CareerPlan;
use App\Domains\Career\Models\CareerSkillCompetencyMapping;
use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\EmployeeLearningRecord;
use App\Domains\Learning\Models\LearningCertificate;
use App\Domains\Organization\Models\Job;
use App\Domains\Performance\Models\PerformanceCompetencyAssessment;
use App\Domains\Performance\Models\PerformanceFinalOutcome;
use App\Domains\Shared\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CareerReadinessEngine
{
    public function __construct(
        protected CareerSkillGapService $skillGapService,
        protected AuditService $audit
    ) {}

    public function calculateSkillMatch(Employee $employee, Job $targetJob): float
    {
        return $this->skillGapService->calculateSkillMatchPercentage($employee, $targetJob);
    }

    public function calculateCompetencyMatch(Employee $employee, Job $targetJob): float
    {
        // Find competencies mapped to skills of target job
        $requiredSkillIds = $targetJob->skillRequirements()->pluck('skill_id');
        $mappedCompetencyIds = CareerSkillCompetencyMapping::query()
            ->where('tenant_id', $employee->tenant_id)
            ->whereIn('skill_id', $requiredSkillIds)
            ->pluck('competency_id')
            ->unique();

        if ($mappedCompetencyIds->isEmpty()) {
            return 100.0;
        }

        $latestAssessments = PerformanceCompetencyAssessment::query()
            ->whereIn('competency_id', $mappedCompetencyIds)
            ->pluck('rating', 'competency_id');

        if ($latestAssessments->isEmpty()) {
            return 60.0; // Baseline average
        }

        $sumRating = 0;
        foreach ($mappedCompetencyIds as $cId) {
            $rating = (float) ($latestAssessments[$cId] ?? 2.5);
            $sumRating += min(5.0, $rating);
        }

        $maxPossible = $mappedCompetencyIds->count() * 5.0;
        return round(($sumRating / $maxPossible) * 100.0, 2);
    }

    public function calculateExperienceMatch(Employee $employee, Job $targetJob, float $minimumYears = 3.0): float
    {
        $tenureYears = $employee->joining_date
            ? Carbon::parse($employee->joining_date)->diffInDays(now()) / 365.25
            : 0.0;

        if ($minimumYears <= 0) {
            return 100.0;
        }

        $ratio = $tenureYears / $minimumYears;
        return round(min(100.0, $ratio * 100.0), 2);
    }

    public function calculateLearningMatch(Employee $employee, Job $targetJob): float
    {
        $records = EmployeeLearningRecord::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'completed')
            ->count();

        $activeCerts = LearningCertificate::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->count();

        $score = ($records * 20.0) + ($activeCerts * 30.0);
        return round(min(100.0, max(20.0, $score)), 2);
    }

    public function calculatePerformanceMatch(Employee $employee): float
    {
        $latestOutcome = PerformanceFinalOutcome::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->latest('finalized_at')
            ->first();

        if (! $latestOutcome) {
            return 70.0; // Default baseline (3.5 / 5.0)
        }

        $rating = (float) $latestOutcome->final_rating;
        return round(min(100.0, ($rating / 5.0) * 100.0), 2);
    }

    public function calculateReadiness(
        Employee $employee,
        Job $targetJob,
        array $weights = [
            'skill' => 0.30,
            'competency' => 0.25,
            'performance' => 0.20,
            'experience' => 0.15,
            'learning' => 0.10,
        ]
    ): array {
        $skillScore = $this->calculateSkillMatch($employee, $targetJob);
        $competencyScore = $this->calculateCompetencyMatch($employee, $targetJob);
        $performanceScore = $this->calculatePerformanceMatch($employee);
        $experienceScore = $this->calculateExperienceMatch($employee, $targetJob);
        $learningScore = $this->calculateLearningMatch($employee, $targetJob);

        $totalScore = (
            ($skillScore * ($weights['skill'] ?? 0.30)) +
            ($competencyScore * ($weights['competency'] ?? 0.25)) +
            ($performanceScore * ($weights['performance'] ?? 0.20)) +
            ($experienceScore * ($weights['experience'] ?? 0.15)) +
            ($learningScore * ($weights['learning'] ?? 0.10))
        );

        $totalScore = round($totalScore, 2);

        $level = match (true) {
            $totalScore >= 90.0 => CareerReadinessLevel::ReadyNow->value,
            $totalScore >= 75.0 => CareerReadinessLevel::Ready->value,
            $totalScore >= 60.0 => CareerReadinessLevel::NearlyReady->value,
            $totalScore >= 40.0 => CareerReadinessLevel::Developing->value,
            default => CareerReadinessLevel::NotReady->value,
        };

        return [
            'score' => $totalScore,
            'level' => $level,
            'breakdown' => [
                'skill_match' => $skillScore,
                'competency_match' => $competencyScore,
                'performance_match' => $performanceScore,
                'experience_match' => $experienceScore,
                'learning_match' => $learningScore,
            ],
            'weights' => $weights,
        ];
    }

    public function updatePlanReadiness(CareerPlan $plan): CareerPlan
    {
        if (! $plan->targetJob || ! $plan->employee) {
            return $plan;
        }

        $readiness = $this->calculateReadiness($plan->employee, $plan->targetJob);
        $prevLevel = $plan->readiness_level;

        $plan->update([
            'readiness_score' => $readiness['score'],
            'readiness_level' => $readiness['level'],
        ]);

        CareerReadinessCalculated::dispatch($plan, $readiness['score'], $readiness['level']);

        if ($prevLevel !== $readiness['level']) {
            CareerReadinessChanged::dispatch($plan, (string) $prevLevel, $readiness['level']);
        }

        return $plan;
    }

    public function overrideReadiness(
        CareerPlan $plan,
        string $newLevel,
        Employee $overridingUser,
        string $reason
    ): CareerPlan {
        $calculatedLevel = $plan->readiness_level;
        $calculatedScore = $plan->readiness_score;

        $plan->update([
            'readiness_level' => $newLevel,
        ]);

        $this->audit->record(
            (string) $plan->tenant_id,
            'CareerReadinessOverridden',
            'readiness_override',
            CareerPlan::class,
            (string) $plan->id,
            null,
            ['readiness_level' => $calculatedLevel, 'readiness_score' => $calculatedScore],
            [
                'readiness_level' => $newLevel,
                'employee_id' => $plan->employee_id,
                'overridden_by' => $overridingUser->id,
                'reason' => $reason,
            ]
        );

        return $plan;
    }
}
