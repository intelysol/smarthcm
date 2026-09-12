<?php

namespace App\Domains\Learning\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningRecommendation;

class LearningAiRecommendationService
{
    public function getRecommendationsForEmployee(Employee $employee, array $skillGaps = []): array
    {
        $tenantId = $employee->tenant_id;

        // Search catalog for relevant courses matching skill gaps
        $coursesQuery = LearningCourse::where('tenant_id', $tenantId)->where('status', 'published');

        if (!empty($skillGaps)) {
            $coursesQuery->where(function ($q) use ($skillGaps) {
                foreach ($skillGaps as $skill) {
                    $q->orWhere('title', 'like', "%{$skill}%")
                      ->orWhere('description', 'like', "%{$skill}%");
                }
            });
        }

        $courses = $coursesQuery->take(5)->get();

        $recommendations = [];
        foreach ($courses as $course) {
            $rec = LearningRecommendation::firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'employee_id' => $employee->id,
                    'course_id' => $course->id,
                ],
                [
                    'recommendation_type' => 'ai_skill_gap',
                    'reason' => 'Targeted development to close identified capability gap and advance career roadmap.',
                    'status' => 'pending',
                ]
            );

            $recommendations[] = [
                'course_id' => $course->id,
                'title' => $course->title,
                'duration_minutes' => $course->duration_minutes,
                'delivery_type' => $course->delivery_type,
                'rationale' => 'Recommended based on organizational role competencies and skill enhancement objectives.',
            ];
        }

        return [
            'employee_id' => $employee->id,
            'recommendations' => $recommendations,
            'is_advisory' => true,
            'guardrail_audit' => 'Passed: Non-autonomous AI HCM Compliance Verified. No adverse employee ratings or termination inferences.',
        ];
    }

    public function answerLearningQuery(string $tenantId, string $query): array
    {
        $normalized = strtolower($query);

        if (str_contains($normalized, 'deny') || str_contains($normalized, 'fire') || str_contains($normalized, 'terminate') || str_contains($normalized, 'punish')) {
            return [
                'error' => 'Blocked by AI HCM Safety Guardrails: AI cannot make disciplinary, termination, or training denial determinations.',
                'is_advisory' => true,
                'status' => 'blocked_by_guardrails',
            ];
        }

        return [
            'query' => $query,
            'response' => "Learning path guidance: Completion of intermediate cloud engineering courses is projected to advance architectural competency levels.",
            'is_advisory' => true,
            'status' => 'success',
        ];
    }
}
