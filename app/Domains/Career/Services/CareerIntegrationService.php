<?php

namespace App\Domains\Career\Services;

use App\Domains\Career\Contracts\CompetencyTalentProvider;
use App\Domains\Career\Contracts\EmployeeTalentProvider;
use App\Domains\Career\Contracts\InternalMobilityProvider;
use App\Domains\Career\Contracts\JobTalentProvider;
use App\Domains\Career\Contracts\LearningTalentProvider;
use App\Domains\Career\Contracts\PerformanceTalentProvider;
use App\Domains\Career\Models\CareerJobSkillRequirement;
use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\EmployeeLearningRecord;
use App\Domains\Learning\Models\LearningCertificate;
use App\Domains\Organization\Models\Job;
use App\Domains\Performance\Models\Competency;
use App\Domains\Performance\Models\PerformanceCompetencyAssessment;
use App\Domains\Performance\Models\PerformanceFinalOutcome;
use App\Domains\Performance\Models\PerformanceGoal;
use Carbon\Carbon;

class CareerIntegrationService implements
    PerformanceTalentProvider,
    LearningTalentProvider,
    EmployeeTalentProvider,
    JobTalentProvider,
    CompetencyTalentProvider,
    InternalMobilityProvider
{
    public function __construct(protected CareerJobMatchingService $matchingService) {}

    public function getEmployeePerformance(Employee $employee): array
    {
        $outcomes = PerformanceFinalOutcome::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->latest('finalized_at')
            ->get();

        $goals = PerformanceGoal::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->get(['id', 'title', 'progress_percentage', 'status'])
            ->toArray();

        $latestRating = $outcomes->first() ? (float) $outcomes->first()->final_rating : null;

        return [
            'latest_rating' => $latestRating,
            'performance_history' => $outcomes->toArray(),
            'competencies' => $this->getEmployeeCompetencies($employee),
            'goals' => $goals,
        ];
    }

    public function getEmployeeLearning(Employee $employee): array
    {
        $records = EmployeeLearningRecord::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->with('course')
            ->get();

        $certs = LearningCertificate::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->get();

        $totalCredits = (float) $records->sum('credits_awarded');
        $totalHours = (float) $records->sum('learning_hours');

        return [
            'completed_courses' => $records->toArray(),
            'certificates' => $certs->toArray(),
            'total_credits' => $totalCredits,
            'learning_hours' => $totalHours,
        ];
    }

    public function getEmployeeSummary(Employee $employee): array
    {
        $tenureYears = $employee->joining_date
            ? Carbon::parse($employee->joining_date)->diffInDays(now()) / 365.25
            : 0.0;

        return [
            'id' => (string) $employee->id,
            'name' => "{$employee->first_name} {$employee->last_name}",
            'department' => $employee->department?->department_name,
            'job' => $employee->designation?->title ?? $employee->position?->title ?? 'Team Member',
            'tenure_years' => round($tenureYears, 1),
            'joining_date' => $employee->joining_date?->toDateString(),
        ];
    }

    public function getJobRequirements(Job $job): array
    {
        $skills = CareerJobSkillRequirement::query()
            ->where('tenant_id', $job->tenant_id)
            ->where('job_id', $job->id)
            ->with('skill')
            ->get()
            ->toArray();

        return [
            'skills' => $skills,
            'competencies' => [],
            'minimum_experience_years' => (float) ($job->minimum_experience ?? 2.0),
        ];
    }

    public function getEmployeeCompetencies(Employee $employee): array
    {
        $assessments = PerformanceCompetencyAssessment::query()
            ->whereHas('review', fn ($q) => $q->where('employee_id', $employee->id))
            ->with('competency')
            ->get();

        $result = [];
        foreach ($assessments as $a) {
            if ($a->competency) {
                $result[$a->competency_id] = [
                    'id' => (string) $a->competency_id,
                    'name' => $a->competency->name,
                    'rating' => (float) $a->rating,
                    'level' => (int) round($a->rating),
                ];
            }
        }

        return $result;
    }

    public function findMatchingOpportunities(Employee $employee): array
    {
        return $this->matchingService->findMatchingJobsForEmployee($employee)->toArray();
    }
}
