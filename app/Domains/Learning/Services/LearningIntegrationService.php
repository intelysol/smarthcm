<?php

namespace App\Domains\Learning\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Events\LearningDevelopmentRecommendationCreated;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningPath;
use App\Domains\Learning\Models\LearningProgram;
use App\Domains\Learning\Models\LearningRecommendation;
use App\Domains\Performance\Contracts\PerformanceLearningProvider;

class LearningIntegrationService implements PerformanceLearningProvider
{
    /**
     * Return recommended learning for an employee based on performance review / competency gaps.
     *
     * @return list<array<string, mixed>>
     */
    public function recommendedLearning(string $employeeId, string $cycleId): array
    {
        $employee = Employee::query()->find($employeeId);
        if (! $employee) return [];

        $recommendations = LearningRecommendation::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employeeId)
            ->where('status', 'pending')
            ->with(['course', 'program', 'path'])
            ->get();

        return $recommendations->map(fn (LearningRecommendation $rec) => [
            'id' => $rec->id,
            'source' => $rec->source,
            'reason' => $rec->reason,
            'course' => $rec->course ? [
                'id' => $rec->course->id,
                'title' => $rec->course->title,
                'delivery_type' => $rec->course->delivery_type,
                'duration' => $rec->course->duration,
            ] : null,
            'program' => $rec->program ? [
                'id' => $rec->program->id,
                'title' => $rec->program->title,
            ] : null,
            'path' => $rec->path ? [
                'id' => $rec->path->id,
                'title' => $rec->path->title,
            ] : null,
        ])->values()->all();
    }

    /**
     * Create learning recommendation from a Performance competency gap.
     */
    public function createCompetencyRecommendation(
        Employee $employee,
        string $competencyId,
        ?LearningCourse $course = null,
        ?string $reason = null,
        ?Employee $recommendedBy = null
    ): LearningRecommendation {
        $recommendation = LearningRecommendation::query()->create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'course_id' => $course?->id,
            'source' => 'competency_gap',
            'competency_id' => $competencyId,
            'recommended_by' => $recommendedBy?->id,
            'reason' => $reason ?? 'Recommended to address identified competency gap.',
            'status' => 'pending',
        ]);

        LearningDevelopmentRecommendationCreated::dispatch($recommendation);

        return $recommendation;
    }
}
